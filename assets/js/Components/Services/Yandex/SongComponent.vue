<template>
    <div class="pt-4">
        <input-song-component @next-step="displayTrackInfo" v-if="isInputUrlComponent" :track-id="trackId" />
        <song-details-component v-if="isTrackInfoComponent" :details="trackDetails" @request="onRequest" />
    </div>
</template>

<script>
    import InputSongComponent from "./Song/InputSongComponent";
    import SongDetailsComponent from "./Song/SongDetailsComponent";

    const INPUT_URL_COMPONENT = 1;
    const DISPLAY_INFO_COMPONENT = 2;

    export default {
        props: {
            trackIdProp: {
                type: Number,
                required: false
            }
        },

        data() {
            return {
                component: INPUT_URL_COMPONENT,
                trackDetails: [],
                trackId: this.trackIdProp
            }
        },

        computed: {
            isInputUrlComponent() {
                return this.component === INPUT_URL_COMPONENT
            },
            isTrackInfoComponent() {
                return this.component === DISPLAY_INFO_COMPONENT
            }
        },

        methods: {
            onRequest(id) {
                this.trackId = id;
                this.component = INPUT_URL_COMPONENT;
            },
            displayTrackInfo(info) {
                this.$router.push({name: 'yandex.song', params: {song: info.id}});

                this.trackDetails = info;
                this.component = DISPLAY_INFO_COMPONENT;
            }
        },

        components: {SongDetailsComponent, InputSongComponent}
    }
</script>

<style scoped>

</style>
