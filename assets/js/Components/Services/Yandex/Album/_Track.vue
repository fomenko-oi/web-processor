<template>
    <a :href="url" class="row" target="_blank">
        <div class="col-md-9">
            {{ track.title }}
            <span class="badge badge-secondary">{{ millisToMinutesAndSeconds(track.duration) }}</span>
        </div>

        <span class="badge badge-light" v-if="isProgress">
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        </span>

        <div class="col-md-3" v-if="isSuccess">
            <a class="btn btn-xs badge-success" :href="route(`yandex.song.frontend.download.${this.$i18n.locale.toLowerCase()}`, {fileId: id})">
                <i class="material-icons hoverable" style="font-size: 14px;" title="Download">play_for_work</i>
            </a>

            <a class="btn btn-xs badge-warning" :href="downloadUrl" target="_blank">
                <i class="material-icons hoverable" style="font-size: 14px;" title="Play">play_arrow</i>
            </a>
        </div>
    </a>
</template>

<script>
    export default {
        props: ['track', 'status', 'info'],

        computed: {
            url() {
                return this.route(`yandex.song.index.${this.$i18n.locale.toLowerCase()}`, {trackId: this.track.id})
            },
            downloadUrl() {
                return this.info ? `/storage/${this.info.path}` : null
            },
            isSuccess() {
                return this.info && this.info.status === 'success'
            },
            isProgress() {
                return this.info && (this.info.status === 'progress' || this.info.status === 'new')
            },
            id() {
                return this.info ? this.info.id.value : null
            }
        },

        methods: {
            millisToMinutesAndSeconds(millis) {
                const minutes = Math.floor(millis / 60000);
                const seconds = ((millis % 60000) / 1000).toFixed(0);
                return minutes + ":" + (seconds < 10 ? '0' : '') + seconds;
            },
        }
    }
</script>

<style scoped>

</style>
