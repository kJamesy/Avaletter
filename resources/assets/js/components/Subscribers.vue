<template>
    <div class="subscribers">
        <vue-progress-bar></vue-progress-bar>
        <subscribers-nav></subscribers-nav>

        <transition>
            <router-view></router-view>
        </transition>
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
