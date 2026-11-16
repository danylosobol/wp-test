import js from "@eslint/js"
import stylistic from "@stylistic/eslint-plugin"
import globals from "globals"

export default [
	{
		ignores: [
			"**/node_modules/",
			"**/vendor/",
			"**/dist/",
			"wp-content/plugins/",
			"wp-content/mu-plugins/",
			"wordpress/",
		],
	},
	js.configs.recommended,
	{
		files: ["**/*.{js,jsx}"],
		languageOptions: {
			ecmaVersion: "latest",
			sourceType: "module",
			globals: {
				...globals.browser,
				...globals.node,
				// Theme globals defined in assets/js/scripts.js and wp_localize_script().
				SobolDevTheme: "readonly",
				SobolDevBlockBase: "readonly",
				SobolDevThemeVars: "readonly",
				acf: "readonly",
			},
		},
		plugins: {
			"@stylistic": stylistic,
		},
		rules: {
			"@stylistic/indent": ["error", "tab"],
			"@stylistic/linebreak-style": ["error", "unix"],
			"@stylistic/quotes": ["error", "double"],
			"@stylistic/semi": ["error", "never"],
			"@stylistic/space-before-function-paren": ["error", "always"],
			"@stylistic/no-multi-spaces": ["error", {}],
			"@stylistic/space-in-parens": ["error", "never"],
			"@stylistic/comma-spacing": ["error", { before: false, after: true }],
			"curly": ["error", "all"],
			"@stylistic/object-curly-newline": ["error", { multiline: true }],
			"@stylistic/object-curly-spacing": ["error", "always"],
		},
	},
]
