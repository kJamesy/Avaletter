<template>
    <div class="subscribers-nav">
        <router-link :to="{ name: 'subscribers' }">All Subscribers</router-link>
        <router-link :to="{ name: 'subscribers.new' }">New Subscriber</router-link>
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
