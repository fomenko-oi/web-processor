<template>
    <div class="row">
        <div class="info-text col-md-6">
            <p>Pass URL in input below and download the song.</p>

            <form :action="route('yandex.song.process')" class="mt-5" method="POST" @submit.prevent="onSubmit">
                <div class="form-group">
                    <div class="input-group">
                        <input
                                type="text"
                                :class="{'form-control': true, 'is-invalid': isInvalidId}"
                                name="url"
                                placeholder="https://music.yandex.ru/album/10130567/track/63591534"
                                v-model="url"
                        >

                        <button class="btn btn-warning" type="button" :disabled="isButtonDisabled" @click="onSubmit">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" v-if="loading"></span>
                            <span v-else>Download</span>
                        </button>

                        <span class="invalid-feedback" role="alert" v-if="isInvalidId">
                            <strong>Id is invalid</strong>
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
        data() {
            return {
                url: '',
                loading: false,
                error: false
            }
        },

        computed: {
            isButtonDisabled() {
                return !this.url || (this.url && new RegExp(/.*\/[0-9]{1,}/).test(this.url) === false) || this.loading;
            },
            isInvalidId() {
                return this.error
            },
            songId() {
                let data = this.url.match(/.*\/([0-9]{1,})/);

                return data && data.length > 0 ? parseInt(data[1]) : null;
            }
        },

        methods: {
            onSubmit() {
                this.loading = true;
                this.error = false;

                axios.post(this.route('yandex.song.info'), {id: this.songId})
                    .then(res => {
                        if(res.data.success === false) {
                            alert('Something went wrong.');
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
    }
</script>

<style scoped>

</style>
