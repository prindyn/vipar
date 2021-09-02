const { VueLoaderPlugin } = require('vue-loader')
const path = require('path')

module.exports = {
    watch: true,
    watchOptions: {
        aggregateTimeout: 1000
    },
    entry: './resources/app.js',
    module: {
        rules: [
            { test: /\.(js)$/, use: 'babel-loader' },
            { test: /\.(css)$/, use: ['style-loader', 'css-loader'] },
            { test: /\.(vue)$/, use: 'vue-loader' },
            { test: /\.s(a|c)ss$/, use: ['style-loader', 'css-loader', 'sass-loader'] }
        ]
    },
    output: {
        path: path.resolve(__dirname, '../catalog/view/theme/custom/js'),
        filename: 'app.js'
    },
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm.js'
        }
    },
    plugins: [
        new VueLoaderPlugin()
    ]
}