<template>
    <div>
        <p>Pass URL in input below and download the song.</p>

        <form :action="route('yandex.song.process')" class="mt-5" method="POST">
            <div class="form-group">
                <div class="input-group">
                    <input type="text" class="form-control" name="url" placeholder="https://music.yandex.ru/album/10130567/track/63591534">
                    <input type="submit" class="btn btn-warning" value="Download">
                </div>
            </div>
        </form>
    </div>
</template>

<script>
    export default {
        created() {
            console.log('created song component')
        }
    }
</script>

<style scoped>

</style>
