<template>
    <div class="emails-nav">
        <router-link v-bind:to="{ name: 'emails.index' }" exact>All Emails</router-link> |
        <router-link v-bind:to="{ name: 'emails.new' }">New Email</router-link>
    </div>
</template>