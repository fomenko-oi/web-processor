<template>
    <div class="row mt-1">
        <div :class="{'col-md-8': isSuccess, 'col-md-12': isProgress || isNew}">
            <i class="badge badge-primary">{{ song.title }}, {{ item.bitrate }} kbps</i>

            <span class="badge badge-success" v-if="isSuccess">Finished</span>
            <span class="badge badge-info" v-if="isProgress">
                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                In progress - {{ item.progress }}%
            </span>
            <span class="badge badge-secondary" v-if="isNew">New</span>
        </div>

        <div class="col-md-4" v-if="isSuccess">
            <a class="btn btn-xs badge-success" :href="route('yandex.frontend.song.download', {fileId: id})">
                <i class="material-icons hoverable" style="font-size: 14px;" title="Download">play_for_work</i>
            </a>

            <a class="btn btn-xs badge-warning" :href="url" target="_blank">
                <i class="material-icons hoverable" style="font-size: 14px;" title="Play">play_arrow</i>
            </a>
        </div>
    </div>
</template>

<script>
    const STATUS_SUCCESS = 'success';
    const STATUS_PROGRESS = 'progress';
    const STATUS_NEW = 'new';

    export default {
        props: ['item', 'song'],

        computed: {
            isSuccess() {
                return this.item.status === STATUS_SUCCESS
            },
            isNew() {
                return this.item.status === STATUS_NEW
            },
            isProgress() {
                return this.item.status === STATUS_PROGRESS
            },
            url() {
                return `/storage/${this.item.path}`
            },
            id() {
                return this.item.id.value
            }
        },

        methods: {
            async recheckStatus() {
                await axios.post(this.route('yandex.song.status'), {id: this.id})
                    .then(res => {
                        const item = res.data.data;

                        this.$emit('update', item);

                        if(item.status === STATUS_SUCCESS) {
                            window.open(this.route('yandex.frontend.song.download', {fileId: this.id}));
                            return;
                        }

                        setTimeout(this.recheckStatus, 3000);
                    })
            }
        },

        mounted() {
            this.recheckStatus();
        }
    }
</script>

<style scoped>

</style>
