<template>
    <div class="subscribers-nav">
        <router-link v-bind:to="{ name: 'subscribers.index' }" exact>All Subscribers</router-link>
        <router-link v-bind:to="{ name: 'subscribers.new' }">New Subscriber</router-link>
    </div>
</template>
<script>
    export default{
        data(){
            return{
                msg:'Subscribers Nav'
            }
        }
    }
</script>
