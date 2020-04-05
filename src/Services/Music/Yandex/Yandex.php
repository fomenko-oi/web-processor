<?php

namespace App\Services\Music\Yandex;

use App\Services\Music\Request\Song;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;

class Yandex
{
    const BASE_URL = 'https://api.music.yandex.net';
    const BASE_OATH_URL = 'https://oauth.yandex.ru';

    /**
     * @var Song
     */
    public $song;
    /**
     * @var BaseClient
     */
    public BaseClient $downloader;
    /**
     * @var BaseClient
     */
    public BaseClient $parser;

    public function __construct(BaseClient $downloader, BaseClient $parser)
    {
        $this->downloader = $downloader;
        $this->parser = $parser;

        $this->song = new Song($this);
    }

    public function downloadFile(string $url, string $savePath, ?callable $progressHandler = null)
    {
        $this->downloader->getClient()->get($url, [
            'save_to' => $savePath,
            RequestOptions::PROGRESS => $progressHandler,
            RequestOptions::CONNECT_TIMEOUT => null,
            RequestOptions::TIMEOUT => null,
            RequestOptions::READ_TIMEOUT => null,
        ]);
    }

    public function login(?string $token = null)
    {
        if($token) {
            $this->client->setToken($token);
            return;
        }
        //$this->client->isCacheEnabled()

        dd($this->getToken());

        $this->client->setToken($this->getToken());
    }

    // TODO move the login to strategies
    public function loginByCredentials(string $login, string $password)
    {
        $this->login = $login;
        $this->password = $password;

        dd($this->getToken());

        $this->client->setToken($this->getToken());
    }

    public function loginByToken(string $token)
    {
        $this->downloader->setToken($token);
        $this->parser->setToken($token);
    }

    /**
     * Restore token for current user from cache
     *
     * @author Donii Sergii <doniysa@gmail.com>
     */
    protected function restoreTokenFromCache()
    {
        if (file_exists($file = $this->_cachePath . "/{$this->config->user->USERNAME}.json")) {

            $createTime = filemtime($file);

            $data = json_decode(file_get_contents($file), true);

            if ((time() - $createTime) > $data['token_expire']) {
                return;
            }

            $this->config->user->TOKEN = $data['token'];
            $this->config->user->TOKEN_EXPIRE = $data['token_expire'];
        }
    }

    /**
     * Prepare request
     *
     * @param string $url    Url
     * @param array  $data   Request data
     * @param string $method Request method (delete|put|get|post|patch)
     *
     * @return \Guzzle\Http\Message\RequestInterface
     *
     * @author Donii Sergii <doniysa@gmail.com>
     */
    protected function _prepareRequest(string $url, array $data = [], string $method = 'get')
    {
        $_data = $method == 'get' ? null : $data;

        $options = [
            $url,
            $this->getAuthHeaders(),
        ];

        $requestOptions = [];

        if ($this->_proxy) {
            $requestOptions['proxy'] = $this->_proxy;
        }

        if ($_data) {
            $options[] = $_data;
        }

        if ($method === 'get') {
            $pOptions = [];

            if (count($data)) {
                $pOptions['query'] = $data;
            }

            $requestOptions = array_merge($pOptions, $requestOptions);
        }

        $options[] = $requestOptions;

        return call_user_func_array([$this->client, $method], $options);
    }

    /**
     * Get auth token for user
     */
    protected function getToken()
    {
        $url = self::BASE_OATH_URL . '/token';

        $resp = $this->client->getClient()->post($url, [
            RequestOptions::FORM_PARAMS => [
                'grant_type' => 'password',
                'client_id' => $this->client->getClientId(),
                'client_secret' => $this->client->getClientSecret(),
                'username' => $this->client->getLogin(),
                'password' => $this->client->getPassword(),
                'x_captcha_answer' => 106435,
                'x_captcha_key' => '00AJCun5u6GJaDD9koRqI0ICEn7ciI1b',
            ],
            RequestOptions::HEADERS => [
                'X-Yandex-Music-Client' => 'WindowsPhone/3.17',
                'User-Agent' => 'Windows 10',
                'Connection' => 'Keep-Alive'
            ]
        ]);

        $data = \GuzzleHttp\json_decode($resp->getBody(), true);

        dd($data);

        $token = $data['access_token'];

        return $token;


        $resp = $this->client->getClient()->post(self::BASE_OATH_URL . '/1/token', [
            RequestOptions::FORM_PARAMS => [
                'grant_type'    => 'x-token',
                'access_token'  => $data['access_token'],
                'client_id'     => $this->client->getClientId(),
                'client_secret' => $this->client->getClientSecret(),
            ]
        ]);

        dd($resp->getBody()->__toString());

        // {"token_type": "bearer", "access_token": "AgAAAAAGGRgzAAG8Xje4LxlOtEGpu8jkCE8RGY8", "expires_in": 31536000, "uid": 102307891}
        $uid = $resp['uid'];

        // TODO check code above and write own
        dd('test');
        $resp = $client->post('1/token', null, [
            'grant_type'    => 'x-token',
            'access_token'  => $resp['access_token'],
            'client_id'     => $this->config->oauth_token->CLIENT_ID,
            'client_secret' => $this->config->oauth_token->CLIENT_SECRET,
        ], $options)->send()->json();

        $this->config->user->TOKEN = $resp['access_token'];
        $this->config->user->TOKEN_EXPIRE = $resp['expires_in'];

        file_put_contents($this->_cachePath . "{$this->config->user->USERNAME}.json", json_encode([
            'token'        => $this->config->user->TOKEN,
            'token_expire' => $this->config->user->TOKEN_EXPIRE,
        ]));
    }

    /**
     * Get auth headers
     *
     * @return array
     */
    public function getAuthHeaders(): array
    {
        return [
            'Authorization'   => "OAuth {$this->token}",
            'Accept Language' => 'en-US,en;q=0.8',
            'Accept Encoding' => 'gzip, deflate, sdch, br',
            'Accept'          => '*/*',
            'Postman Token'   => '0602916c-c9be-3364-8938-6b4f5426539e',
            'User Agent'      => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36',
            'Cache Control'   => 'no-cache',
            'Connection'      => 'keep-alive',
        ];
    }

    /**
     * GET: /account/status
     * Get account status for current user
     *
     * @return array
     *
     * @author Donii Sergii <doniysa@gmail.com>
     */
    public function getAccountStatus(): array
    {
        return $this->_prepareRequest('account/status')->send()->json();
    }

    public function getFeed(): array
    {
        $res = $this->client->getClient()->get(self::BASE_URL . '/feed', [
            RequestOptions::HEADERS => [
                'Authorization' => 'OAuth ' . $this->token,
            ],
            RequestOptions::COOKIES => new CookieJar(),
            RequestOptions::ALLOW_REDIRECTS => 0
        ]);

        return \GuzzleHttp\json_decode($res->getBody()->__toString(), true);
    }

    /**
     * GET: /genres
     * Get a list of music genres
     *
     * @return array
     *
     * @author Donii Sergii <doniysa@gmail.com>
     */
    public function getGenres(): array
    {
        return $this->_prepareRequest('genres')->send()->json();
    }

    /**
     * GET: /search
     * Search artists, tracks, albums.
     *
     * @param string $text The search query
     * @param int    $page Page number
     * @param string $type One from (artist|album|track|all)
     * @param bool   $nococrrect
     *
     * @return array
     *
     * @author Donii Sergii <doniysa@gmail.com>
     */
    public function search($text, $page = 0, $type = 'all', $nococrrect = false): array
    {
        $token = $this->getToken();

        $res = $this->client->getClient()->get(self::BASE_URL . '/account/status', [
            RequestOptions::HEADERS => [
                'Authorization: OAuth '.$token
            ]
        ]);

        die($res->getBody()->__toString());


        $url = self::BASE_URL . '/search?' . http_build_query([
            'type'       => $type,
            'text'       => $text,
            'page'       => $page,
            'nococrrect' => $nococrrect
        ]);
        $url = self::BASE_URL . '/account/status';

        $res = $this->client->getClient()->get($url, [
            RequestOptions::HEADERS => $this->getAuthHeaders()
        ]);

        die($res->getBody());

        return $this->_prepareRequest('search', [
            'type'       => $type,
            'text'       => $text,
            'page'       => $page,
            'nococrrect' => $nococrrect,
        ])->send()->json();
    }

    /**
     * GET: /users/[user_id]/playlists/list
     * Get a user's playlists.
     *
     * @param string $userID The user ID, if null then equal to current user id
     *
     * @return array
     *
     * @author Donii Sergii <doniysa@gmail.com>
     */
    public function getUserPlayLists($userID = null): array
    {
        return $this->getPlayList('list', $userID);
    }

    /**
     * GET: /users/[user_id]/playlists/[playlist_kind]
     * Get a playlist without tracks
     *
     * @param string      $playListKind The playlist ID
     * @param string|null $userID       The user ID, if null then equal to current user id
     *
     * @return array
     *
     * @author Donii Sergii <doniysa@gmail.com>
     */
    public function getPlayList($playListKind, $userID = null): array
    {
        return $this->_prepareRequest('/users/' . ($userID ?? $this->config->user->UID) . "/playlists/{$playListKind}")->send()->json();
    }

    /**
     * GET: /users/[user_id]/playlists
     * Get an array of playlists with tracks
     *
     * @param array       $playlists The playlists IDs. Example: [1,2,3]
     * @param bool        $mixed
     * @param bool        $richTracks
     * @param null|string $userID    The user ID, if null then equal to current user id
     *
     * @return array
     *
     * @author Donii Sergii <doniysa@gmail.com>
     */
    public function getPlayLists(array $playlists, $mixed = false, $richTracks = false, $userID = null): array
    {
        return $this->_prepareRequest('/users/' . ($userID ?? $this->config->user->UID) . "/playlists", [
            'kinds'       => $playlists,
            'mixed'       => $mixed,
            'rich-tracks' => $richTracks,
        ])->send()->json();
    }

    /**
     * POST: /users/[user_id]/playlists/create
     * Create a new playlist
     *
     * @param string $name       The name of the playlist
     * @param string $visibility Visibility level. One of (public|private)
     *
     * @return array
     *
     * @author Donii Sergii <doniysa@gmail.com>
     */
    public function createPlaylist($name, $visibility = 'private'): array
    {
        return $this->_prepareRequest('/users/' . ($userID ?? $this->config->user->UID) . "/playlists/create", [
            'title'      => $name,
            'visibility' => $visibility,
        ])->send()->json();
    }

    /**
     * POST: /users/[user_id]/playlists/[playlist_kind]/delete
     * Remove a playlist
     *
     * @param string $playlistKind
     *
     * @return array
     *
     * @author Donii Sergii <doniysa@gmail.com>
     */
    public function removePlaylist($playlistKind): array
    {
        return $this->_prepareRequest('/users/' . ($userID ?? $this->config->user->UID) . "/playlists/{$playlistKind}/delete")->send()->json();
    }

    public function renamePlaylist($playlistKind, $name): array
    {
        return $this->_prepareRequest('/users/' . ($userID ?? $this->config->user->UID) . "/playlists/{$playlistKind}/name", [
            'value' => $name,
        ])->send()->json();
    }

    /**
     * POST: /users/[user_id]/playlists/[playlist_kind]/change-relative
     * Add tracks to the playlist
     *
     * @param string $playlistKind The playlist's ID
     * @param array  $tracks       An array of objects containing a track info:
     *                             track id and album id for the track.
     *                             Example: [{id:'20599729', albumId:'2347459'}]
     * @param string $revision     Operation id for that request
     * @param int    $at
     * @param string $op           Operation
     *
     * @return array
     *
     * @author Donii Sergii <doniysa@gmail.com>
     */
    public function addTrackToPlaylist($playlistKind, $tracks, $revision, $at = 0, $op = 'insert'): array
    {
        return $this->_prepareRequest('/users/' . ($userID ?? $this->config->user->UID) . "/playlists/{$playlistKind}/change-relative", [
            'diff'     => json_encode([
                'op'     => $op,
                'at'     => $at,
                'tracks' => $tracks,
            ]),
            'revision' => $revision,
        ])->send()->json();
    }

    /**
     * POST: /users/[user_id]/playlists/[playlist_kind]/change-relative
     * Remove tracks from the playlist
     *
     * @param string $playlistKind Th   e playlist's ID
     * @param array  $tracks       An array of objects containing a track info:
     *                             track id and album id for the track.
     *                             Example: [{id:'20599729', albumId:'2347459'}]
     * @param string $revision     Operation id for that request
     * @param int    $at
     * @param string $op           Operation
     *
     * @return array
     *
     * @author Donii Sergii <doniysa@gmail.com>
     */
    public function removeTracksFromPlaylist($playlistKind, $tracks, $revision, $at = 0): array
    {
        return $this->addTrackToPlaylist($playlistKind, $tracks, $revision, $at, 'delete');
    }
}
