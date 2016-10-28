<template>
    <div class="templates">
        <vue-progress-bar></vue-progress-bar>
        <email-templates-nav></email-templates-nav>

        <transition name='slide-right'>
            <router-view></router-view>
        </transition>
    </div>
</template>
<script>
    import EmailTemplatesNav from  '../components/EmailTemplatesNav.vue';

    export default {
        components: {
            EmailTemplatesNav
        }
    }
</script>
