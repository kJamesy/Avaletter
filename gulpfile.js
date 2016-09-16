
const elixir = require('laravel-elixir');

require('laravel-elixir-vue');
require('laravel-elixir-webpack-official');

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

// elixir.webpack.mergeConfig({
//     module: {
//         loaders: [
//             {include: /\.json$/, loaders: ["json-loader"]}
//         ]
//     }
// });

elixir(function(mix) {
    mix.sass('app.scss')
        .styles([
            './public/css/app.css',
            './node_modules/sweetalert/dist/sweetalert.css',
            // './node_modules/sweetalert/themes/twitter/twitter.css'
        ], './public/css/app.css')
        .webpack('app.js');
});