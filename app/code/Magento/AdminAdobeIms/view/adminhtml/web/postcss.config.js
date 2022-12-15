module.exports = {
    plugins: [
        require('postcss-import'),
        require('postcss-varfallback'),
        require('postcss-dropunusedvars'),
        require('cssnano')
    ]
};
