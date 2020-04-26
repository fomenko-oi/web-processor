<template>
    <div class="row">
        <div class="info-text col-md-6">
            <p v-html="$t('yandex.download_title')"></p>

            <form :action="route(`yandex.album.process.${$i18n.locale.toLowerCase()}`)" class="mt-5" method="POST" @submit.prevent="onSubmit" @keyup.enter="onSubmit">
                <div class="form-group">
                    <div class="input-group">
                        <input
                                type="text"
                                :class="{'form-control': true, 'is-invalid': isInvalidId}"
                                name="url"
                                placeholder="https://music.yandex.ru/album/10130567"
                                v-model="url"
                        >

                        <button class="btn btn-warning" type="button" :disabled="isButtonDisabled" @click="onSubmit">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="loading"></span>
                            <span v-else>{{ $t('button.download') }}</span>
                        </button>

                        <span class="invalid-feedback" role="alert" v-if="isInvalidId">
                            <strong>{{ $t('error.invalidId') }}</strong>
                        </span>
                    </div>
                </div>
            </form>
        </div>
        <div class="info-image col-md-6"></div>
    </div>
</template>

<script>
    export default {
        props: ['albumPropId'],

        data() {
            return {
                url: '',
                loading: false,
                error: false
            }
        },

        computed: {
            isButtonDisabled() {
                return !this.url || (this.url && !this.albumId) || this.loading;
            },
            isInvalidId() {
                return this.error
            },
            albumId() {
                if (/^\d+$/.test(this.url)) {
                    return parseInt(this.url)
                }

                let data = this.url.match(/album\/(\d+)|^\d+$/);
                return data && data.length > 0 ? parseInt(data[1]) : null;
            }
        },

        methods: {
            onSubmit() {
                if(!this.albumId) {
                    return false;
                }

                this.loading = true;
                this.error = false;

                axios.post(this.route(`yandex.album.info.${this.$i18n.locale.toLowerCase()}`), {id: this.albumId})
                    .then(res => {
                        if(res.data.success === false) {
                            alert(this.$t('error.common'));
                            return;
                        }

                        this.$emit('next-step', res.data.data)
                    })
                    .catch(err => {
                        if(err.response && err.response.status === 400) {
                            // validation error
                            let errors = err.response.violations;
                            this.error = true;
                        }
                    })
                    .finally(() => {
                        this.loading = false
                    });
            }
        },

        watch: {
            albumPropId() {
                this.url = '' + this.albumId;
                this.onSubmit();
            }
        },

        created() {
            if(!this.albumPropId) {
                return;
            }

            this.url = '' + this.albumPropId;
            this.onSubmit();
        }
    }
</script>

<style scoped>

</style>
