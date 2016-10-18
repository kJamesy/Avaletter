<template>
    <div class="subscribers-nav">
        <router-link v-bind:to="{ name: 'subscribers.index' }" exact>All Subscribers</router-link> |
        <router-link v-bind:to="{ name: 'subscribers.new' }">New Subscriber</router-link> |
        <router-link v-bind:to="{ name: 'subscribers.import' }">Batch Import Subscribers</router-link> |
        <router-link v-bind:to="{ name: 'subscribers.trash' }">Trash</router-link>
    </div>
</template>
<script>
    export default{
        data(){
            return{
                batchImportLink: subscribersLinks.batchImport
            }
        }
    }
</script>
