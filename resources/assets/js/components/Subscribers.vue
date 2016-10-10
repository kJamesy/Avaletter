<template>
    <div class="subscribers">
        <subscribers-nav></subscribers-nav>

        <router-view></router-view>
    </div>
</template>
<style>
    .subscribers{
        /*background-color:#ff0000;*/
    }
</style>
<script>
    import SubscribersNav from  '../components/SubscribersNav.vue';

    export default{
        data(){
            return{
                msg:'hello vue'
            }
        },
        components: {
            SubscribersNav
        }
    }
</script>
