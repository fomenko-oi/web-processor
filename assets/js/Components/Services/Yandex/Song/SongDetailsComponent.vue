<template>
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
                        Album:
                        <span
                                v-for="album in details.albums"
                                class="badge badge-primary hoverable ml-1"
                                data-toggle="popover"
                                data-trigger="hover"
                                data-html="true"
                                :title="`Album ${album.title}`"
                                :data-content="getAlbumTemplate(album)"
                        >
                            {{ album.title }} <em>({{ album.year }})</em>

                            <i class="material-icons" style="font-size: 10px; padding-left: 4px;">info</i>
                        </span>
                    </div>

                    <div class="file-info mt-1">
                        Artist:
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
                <div class="card-header file-icon">Download sources</div>
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
                <div class="card-header file-icon">Downloading progress</div>
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
                progress: []
            }
        },

        computed: {
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

                axios.post(this.route('yandex.song.download'), {id: this.details.id, bitrate: bitrate})
                    .then(res => {
                        this.downloads.push(res.data.data);
                    })
                    .catch(err => {
                        console.log(err);
                    })
            },
            getAlbumTemplate(album) {
                let date = dayjs(album.release_date).format('DD MMMM YYYY');

                return [
                    `ID: <b>${album.id}</b>`,
                    `Title: <b>${album.title}</b>`,
                    `Type: <b>${album.type}</b>`,
                    `Release date: <b>${date}</b>`,
                    `Year: <b>${album.year}</b>`,
                    `<img class="info-image" src="//${album.cover}">`,
                ].join('<br>')
            },
            getArtistTemplate(artist) {
                return [
                    `ID: <b>${artist.id}</b>`,
                    `Name: <b>${artist.name}</b>`,
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
