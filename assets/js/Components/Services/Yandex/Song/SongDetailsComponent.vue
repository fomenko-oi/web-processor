<template>
    <div>
        <div class="row mb-3">
            <div class="col-lg-7 col-xl-7">
                <div class="new-comment">
                    <form action="javascript: void(0)" @submit.prevent="$emit('request', songId)">
                        <div class="input-group">
                            <input
                                    type="text"
                                    name="comment"
                                    :class="{
                                    'form-control': true,
                                    'is-invalid': url.length > 0 && isInvalidId,
                                    'search-input': true
                                }"
                                    placeholder="https://music.yandex.ru/album/10130567/track/63591534"
                                    v-model="url"
                            >
                            <div class="input-group-append">
                                <button :class="{
                                        'btn': true,
                                        'btn-secondary': isButtonDisabled,
                                        'btn-success': !isButtonDisabled
                                    }"
                                        type="button"
                                        id="download-another-button"
                                        :disabled="isButtonDisabled"
                                        @click="$emit('request', songId)"
                                >{{ $t('button.download') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7 col-xl-7">
                <div class="card file photo">
                    <div class="card-header file-icon">
                        <img :src="coverUrl" alt="">
                    </div>
                    <div class="card-body file-info">
                        <p>{{ title }}</p>
                        <span class="file-info">ID: {{ details.id }}</span><br>
                        <div class="file-info">
                            {{ $t('common.album') }}:
                            <span
                                    v-for="album in details.albums"
                                    class="badge badge-primary hoverable ml-1"
                                    data-toggle="popover"
                                    data-trigger="hover"
                                    data-html="true"
                                    :title="`${$t('common.album')} ${album.title}`"
                                    :data-content="getAlbumTemplate(album)"
                            >
                            {{ album.title }} <em>({{ album.year }})</em>

                            <i class="material-icons" style="font-size: 10px; padding-left: 4px;">info</i>
                        </span>
                        </div>

                        <div class="file-info mt-1">
                            {{ $t('common.artist') }}:
                            <span
                                    v-for="artist in details.artists"
                                    class="badge badge-warning hoverable"
                                    data-toggle="popover"
                                    data-trigger="hover"
                                    data-html="true"
                                    :title="`Artist ${artist.name}`"
                                    :data-content="getArtistTemplate(artist)"
                            >
                            {{ artist.name }}

                            <i class="material-icons" style="font-size: 10px; padding-left: 4px;">info</i>
                        </span>
                        </div><br>

                        <a class="btn btn-warning" @click="loadLyrics" :disabled="lyricsLoaded" v-if="details.lyrics_available && !lyrics">
                            <span v-if="lyricsLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            {{ $t('common.load_lyrics') }}
                        </a>
                        <div class="col-md-12 alert alert-warning" v-if="lyrics" v-html="lyrics"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5 col-xl-5">
                <div class="card">
                    <div class="card-header file-icon">{{ $t('yandex.download_sources') }}</div>
                    <div class="card-body file-info">
                        <p>
                            <a
                                    v-for="source in sources"
                                    @click.prevent="onDownloadTrack(source.bitrate)"
                                    href=""
                                    :class="{'pl-2': true, 'disabled': isDisabledBitrate(source.bitrate)}"
                            >
                                <span class="badge badge-success">{{ source.bitrate }} <em>kbps</em></span>
                            </a>
                        </p>
                    </div>
                </div>

                <div class="card" v-if="downloads.length > 0">
                    <div class="card-header file-icon">{{ $t('yandex.download_progress') }}</div>
                    <div class="card-body">
                        <download-item
                                v-for="(item, index) in downloads"
                                :item="item"
                                :song="details"
                                :key="item.id.value"
                                @update="onUpdateItem(index, $event)"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import DownloadItem from "./_DownloadItem";

    export default {
        props: {
            details: {required: true, type: Object}
        },

        data() {
            return {
                downloads: [],
                progress: [],
                url: this.details.id,
                lyricsLoaded: false,
                lyricsLoading: false,
                lyrics: ''
            }
        },

        computed: {
            isButtonDisabled() {
                return !this.url || this.isInvalidId || +this.details.id === +this.songId;
            },
            isInvalidId() {
                return !this.songId
            },
            songId() {
                let data = this.url.match(/\/?([0-9]{1,})$/);

                return data && data.length > 0 ? parseInt(data[1]) : null;
            },
            coverUrl() {
                return `https://${this.details.cover}`
            },
            title() {
                return this.details.title
            },
            sources() {
                return [
                    {bitrate: 320, type: 'MP3'},
                    {bitrate: 192, type: 'MP3'},
                ]
            }
        },

        methods: {
            loadLyrics() {
                this.lyricsLoading = true;
                axios.post(this.route(`yandex.song.lyrics.${this.$i18n.locale.toLowerCase()}`, {trackId: this.details.id}))
                    .then(res => {
                        this.lyrics = res.data.data.text
                        this.lyricsLoaded = true
                    })
                    .catch(err => {
                        console.log(err);
                    })
                    .finally(() => {
                        this.lyricsLoading = true;
                    })
            },
            isDisabledBitrate(bitrate) {
                return this.progress.includes(bitrate);
            },
            onUpdateItem(index, payload) {
                this.$set(this.downloads, index, payload);
            },
            onDownloadTrack(bitrate) {
                if(this.progress.includes(bitrate)) {
                    return;
                }
                this.progress.push(bitrate);

                axios.post(this.route(`yandex.song.download.${this.$i18n.locale.toLowerCase()}`), {
                    id: this.details.id, bitrate: bitrate
                })
                    .then(res => {
                        this.downloads.push(res.data.data);
                    })
                    .catch(err => {
                        console.log(err);
                    })
            },
            getAlbumTemplate(album) {
                let date = dayjs(album.release_date).format('DD MM YYYY');

                return [
                    `ID: <b>${album.id}</b>`,
                    `${this.$t('common.title')}: <b>${album.title}</b>`,
                    `${this.$t('common.type')}: <b>${album.type}</b>`,
                    `${this.$t('common.release_date')}: <b>${date}</b>`,
                    `${this.$t('common.year')}: <b>${album.year}</b>`,
                    `<img class="info-image" src="//${album.cover}">`,
                ].join('<br>')
            },
            getArtistTemplate(artist) {
                return [
                    `ID: <b>${artist.id}</b>`,
                    `${this.$t('common.name')}: <b>${artist.name}</b>`,
                    `<img class="info-image" src="//${artist.cover}">`,
                ].join('<br>')
            }
        },

        mounted() {
            $(function () {
                $('[data-toggle="popover"]').popover()
            })
        },

        components: {DownloadItem}
    }
</script>

<style scoped>
    .hoverable:hover {
        cursor: pointer !important;
    }
    a.disabled {
        color: gray;
        opacity: .5;
    }
</style>
