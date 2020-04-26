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
                        <span class="file-info">Release Date: {{ date }}</span><br>

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
                    </div>
                </div>
            </div>

            <div class="col-lg-5 col-xl-5">
                <div class="card">
                    <div class="card-header file-icon">
                        Tracks <span class="badge badge-primary">{{ details.track_count }}</span>

                        <span class="badge badge-light float-right" v-if="isLoading">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        </span>
                        <span class="btn btn-xs badge-success float-right" @click="downloadAlbum" v-else-if="!checkingId">
                            <i title="Download all" class="material-icons hoverable" style="font-size: 14px;">play_for_work</i>
                        </span>
                        <span class="btn btn-xs badge-warning float-right" @click="saveAll" v-else>
                            <i title="Save all" class="material-icons hoverable" style="font-size: 14px;">play_for_work</i>
                        </span>
                    </div>
                    <div class="card-body file-info" style="margin: 0; padding: 0">
                        <div class="row">
                            <div class="col-md-12">
                                <ul class="list-group">
                                    <li class="list-group-item" v-for="track in details.tracks">
                                        <track-item :track="track" :key="track.id" :info="getDownloadInfo(track.id)" />
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import DownloadItem from "./_DownloadItem";
    import TrackItem from "./_Track";

    export default {
        props: {
            details: {required: true, type: Object}
        },

        data() {
            return {
                downloads: [],
                url: this.details.id,
                checkingId: null
            }
        },

        computed: {
            isLoading() {
                return this.checkingId && this.progressCount > 0
            },
            progressCount() {
                if(!this.downloads) {
                    return 0
                }
                return this.downloads.filter(el => el.status === 'new' || el.status === 'progress').length
            },
            isButtonDisabled() {
                return !this.url || this.isInvalidId || +this.details.id === +this.albumId;
            },
            isInvalidId() {
                return !this.albumId
            },
            albumId() {
                if (/^\d+$/.test(this.url)) {
                    return parseInt(this.url)
                }

                let data = this.url.match(/\/?([0-9]{1,})$/);
                return data && data.length > 0 ? parseInt(data[1]) : null;
            },
            coverUrl() {
                return `https://${this.details.cover}`
            },
            title() {
                return this.details.title
            },
            date() {
                return dayjs(this.details.release_date).format('DD.MM.YYYY')
            },
            sources() {
                return [
                    {bitrate: 320, type: 'MP3'},
                    {bitrate: 192, type: 'MP3'},
                ]
            }
        },

        created() {
            if (this.checkingId) {
                this.checkStatus();
            }
        },

        methods: {
            saveAll() {
                if(!this.downloads) {
                    return false;
                }

                this.downloads.map(file => {
                    window.open(this.route(`yandex.song.frontend.download.${this.$i18n.locale.toLowerCase()}`, {
                        fileId: file.id.value
                    }))
                })
            },
            getDownloadInfo(id) {
                return this.downloads.filter(item => parseInt(item.trackId) === parseInt(id))[0] || null
            },
            async checkStatus() {
                if(!this.checkingId || this.progressCount === 0) {
                    return;
                }

                await axios.post(this.route(`yandex.album.status.${this.$i18n.locale.toLowerCase()}`), {
                    id: this.checkingId
                })
                    .then(res => {
                        if(res.data.success === false) {
                            console.log(data);
                            return
                        }

                        this.downloads = res.data.data.tracks;
                        setTimeout(this.checkStatus, 1000);
                    })
                    .catch(err => {
                        console.log(err);
                    })
            },
            downloadAlbum() {
                axios.post(this.route(`yandex.album.download.${this.$i18n.locale.toLowerCase()}`), {
                    id: this.details.id, bitrate: 320
                })
                    .then(res => {
                        this.checkingId = res.data.data.id.value;
                        this.downloads = res.data.data.tracks;

                        setTimeout(this.checkStatus, 2000);
                    })
                    .catch(err => {
                        console.log(err);
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

        components: {DownloadItem, TrackItem}
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
