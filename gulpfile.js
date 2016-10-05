
const elixir = require('laravel-elixir');
require('laravel-elixir-vue-2');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.sass('app.scss')
        .styles([
            './public/css/app.css',
            './node_modules/sweetalert/dist/sweetalert.css',
            // './node_modules/sweetalert/themes/twitter/twitter.css'
            './node_modules/animate.css/animate.min.css'
        ], './public/css/app.css')
        .webpack('app.js');
});