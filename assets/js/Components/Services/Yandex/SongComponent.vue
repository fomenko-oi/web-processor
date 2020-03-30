<template>
    <div class="pt-4">
        <input-song-component @next-step="displayTrackInfo" v-if="isInputUrlComponent" />
        <song-details-component v-if="isTrackInfoComponent" :details="trackDetails" />
    </div>
</template>

<script>
    import InputSongComponent from "./Song/InputSongComponent";
    import SongDetailsComponent from "./Song/SongDetailsComponent";

    const INPUT_URL_COMPONENT = 1;
    const DISPLAY_INFO_COMPONENT = 2;

    export default {
        data() {
            return {
                component: INPUT_URL_COMPONENT,
                trackDetails: [],
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
            displayTrackInfo(info) {
                this.trackDetails = info;
                this.component = DISPLAY_INFO_COMPONENT;
            }
        },

        components: {SongDetailsComponent, InputSongComponent}
    }
</script>

<style scoped>

</style>
