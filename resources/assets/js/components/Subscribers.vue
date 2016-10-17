<template>
    <div class="subscribers">
        <vue-progress-bar></vue-progress-bar>
        <subscribers-nav></subscribers-nav>

        <transition name='slide-right'>
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
        mounted() {

        },
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
