<template>
    <div class="templates-nav">
        <router-link v-bind:to="{ name: 'emailTemplates.index' }" exact>All Email Templates</router-link> |
        <router-link v-bind:to="{ name: 'emailTemplates.new' }">New Email Template</router-link>
    </div>
</template>