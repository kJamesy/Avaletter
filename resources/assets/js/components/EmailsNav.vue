<template>
    <div class="emails-nav">
        <router-link v-bind:to="{ name: 'emails.index' }" exact>Sent Emails</router-link> |
        <router-link v-bind:to="{ name: 'emails.drafts' }" exact>Drafts</router-link> |
        <router-link v-bind:to="{ name: 'emails.new' }">New Email</router-link> |
        <router-link v-bind:to="{ name: 'emails.trash' }" exact>Bin</router-link>
    </div>
</template>