<template>
    <div class="pt-4">
        <input-album-component v-if="isInputUrlComponent" @next-step="displayAlbumInfo" :album-prop-id="albumId" />
        <details-component v-if="isAlbumInfoComponent" :details="albumDetails" @request="onRequest" />
    </div>
</template>

<script>
    import InputAlbumComponent from "./Album/Input";
    import DetailsComponent from "./Album/Details";

    const INPUT_URL_COMPONENT = 1;
    const DISPLAY_INFO_COMPONENT = 2;

    export default {
        props: {
            albumIdProp: {
                type: Number,
                required: false
            }
        },

        data() {
            return {
                component: INPUT_URL_COMPONENT,
                albumDetails: [],
                albumId: this.albumIdProp
            }
        },

        computed: {
            isInputUrlComponent() {
                return this.component === INPUT_URL_COMPONENT
            },
            isAlbumInfoComponent() {
                return this.component === DISPLAY_INFO_COMPONENT
            }
        },

        methods: {
            onRequest(id) {
                this.albumId = id;
                this.component = INPUT_URL_COMPONENT;
            },
            displayAlbumInfo(info) {
                const locale = window.app.locale;
                const currentRoute = this.$router.currentRoute;

                if(currentRoute.name !== 'yandex.album' || currentRoute.params.album !== info.id) {
                    if(locale === 'en') {
                        this.$router.push({name: 'yandex.album', params: {album: info.id}});
                    } else {
                        this.$router.push({name: 'yandex.album.local', params: {
                            album: info.id,
                            lang: locale
                        }});
                    }
                }

                this.albumDetails = info;
                this.component = DISPLAY_INFO_COMPONENT;
            }
        },

        components: {InputAlbumComponent, DetailsComponent}
    }
</script>

<style scoped>

</style>
