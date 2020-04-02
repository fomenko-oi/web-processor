<?php

namespace App\Services\Music\Yandex;

use App\Services\Music\Common\AbstractClient;
use App\Services\Music\Yandex\Middleware\ReloginMiddleware;

class BaseClient extends AbstractClient
{
    const DEFAULT_DEVICE_ID = '377c5ae26b09fccd72deae0a95425559';
    const DEFAULT_DEVICE_UUID = '3cfccdaf75dcf98b917a54afe50447ba';
    const DEFAULT_DEVICE_PACKAGE = 'ru.yandex.music';

    const DEFAULT_CLIENT_ID = '23cabbbdc6cd418abb4b39c32c41195d';
    const DEFAULT_CLIENT_SECRET = '53bc75238f0c4d08a118e51fe9203300';

    /**
     * @var string
     */
    protected $cookieDir;
    /**
     * @var string
     */
    protected $cacheDir = null;

    protected $device;
    protected $clientId;
    protected $clientSecret;

    protected $login;
    protected $password;

    public function __construct(string $login, string $password, string $cookieDir, ?string $proxy = null)
    {
        if($proxy) {
            $this->setProxy($proxy);
        }
        $this->setBaseDomain(Yandex::BASE_URL);

        $this->login = $login;
        $this->password = $password;
        $this->cookieDir = $cookieDir;

        $this->pushMiddleware(ReloginMiddleware::create());
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @param string $login
     */
    public function setLogin(string $login): self
    {
        $this->login = $login;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function setCookieFile(string $name): void
    {
        $this->cookie_file = "{$this->cookieDir}/{$name}";
    }

    public function enableCache(string $cacheDir): void
    {
        $this->cacheDir = $cacheDir;
    }

    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    public function isCacheEnabled(): bool
    {
        return !empty($this->cacheDir);
    }

    public function setDeviceConfigs(string $id, string $uuid, string $package)
    {
        $this->device = [
            'device_id'    => $id,
            'device_uuid'  => $uuid,
            'package_name' => $package
        ];
    }

    public function getDeviceConfigs(): array
    {
        if(!$this->device) {
            $this->setDeviceConfigs(self::DEFAULT_DEVICE_ID, self::DEFAULT_DEVICE_UUID,  self::DEFAULT_DEVICE_PACKAGE);
        }

        return $this->device;
    }

    public function setClientId(string $id)
    {
        $this->clientId = $id;
    }

    public function getClientId(): string
    {
        if(!$this->clientId) {
            return static::DEFAULT_CLIENT_ID;
        }
        return $this->clientId;
    }

    public function setClientSecret(string $secret)
    {
        $this->clientSecret = $secret;
    }

    public function getClientSecret(): string
    {
        if(!$this->clientSecret) {
            return static::DEFAULT_CLIENT_SECRET;
        }
        return $this->clientSecret;
    }
}
