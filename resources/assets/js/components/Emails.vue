<template>
    <div class="templates">
        <vue-progress-bar></vue-progress-bar>
        <emails-nav></emails-nav>

        <transition name='slide-right'>
            <router-view></router-view>
        </transition>
    </div>
</template>
<script>
    import EmailsNav from  '../components/EmailsNav.vue';

    export default {
        components: {
            EmailsNav
        }
    }
</script>
