let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.autoload({
    jquery: ['$', 'window.jQuery']
});
mix.js('resources/assets/js/app.js', 'public/js')
   .sass('resources/assets/sass/app.scss', 'public/css');

mix.copy('resources/assets/js/jquery-ui.min.js', 'public/js/jquery-ui.min.js');
mix.copy('resources/assets/sass/ui.css', 'public/css/ui.css');

mix.babel('resources/assets/js/scripts.bundle.js', 'public/js/scripts.bundle.js');
mix.babel('resources/assets/js/ui.js', 'public/js/ui.js');

mix.copyDirectory('resources/assets/images', 'public/images');
mix.copyDirectory('resources/assets/theme/dist/default/assets/', 'public/assets');
