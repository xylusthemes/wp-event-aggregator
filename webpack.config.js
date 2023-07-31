const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
	...defaultConfig,
	output: {
		...defaultConfig.output,
		path: path.resolve(__dirname, 'assets/js/blocks'),
	},
	entry: {
		'gutenberg.blocks': [ './blocks/index.js' ],
	},
	module: {
		rules: [
			...defaultConfig.module.rules,
			{
				test: /\.svg$/,
				use: [{
					loader: 'svg-react-loader'
				}]
			}
		],
	},
	externals: {
		react: 'React'
	},
};