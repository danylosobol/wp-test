import { defineConfig } from "vite"
import { fileURLToPath, URL } from "url";
import alias from "@rollup/plugin-alias"
import autoprefixer from "autoprefixer"
import fs from "fs"
import dotenv from "dotenv"
import glob from "glob"
import svgr from "vite-plugin-svgr"
// import { dependencies } from "./package.json"
import reactRefresh from "@vitejs/plugin-react-refresh"
import handlebars from 'vite-plugin-handlebars';
import replace from '@rollup/plugin-replace';

dotenv.config()

const themeRoot = "wp-content/themes/soboldev"
const prod = (process.env.SITE_ENV == "production")
// https://vitejs.dev/config
export default defineConfig({
	define: { global: "globalThis" },
	assetsInclude: ['**/*.hbs'],
	plugins: [
		alias(),
		handlebars(),
		reactRefresh(),
		svgr({ svgrOptions: { exportType: "default", }, }),
		...(!prod ? [await import("vite-plugin-live-reload").then((module) => module.liveReload(__dirname + themeRoot + "/**/*.{php,js,jsx,css,json,scss}"))] : []),
		replace({
			...(!prod ? {} : {
				"static DEBUG = true": "static DEBUG = false",
			}),
		}),
	],
	css: {
		postcss: {
			plugins: [
				autoprefixer({})
			],
		}
	},
	resolve: {
		alias: [
			{
				find: "~blocks",
				replacement: fileURLToPath(new URL(themeRoot + "/blocks", import.meta.url)),
			},
			{
				find: "~css",
				replacement: fileURLToPath(new URL(themeRoot + "/assets/css/", import.meta.url)),
			},
			{
				find: "~fonts",
				replacement: fileURLToPath(new URL(themeRoot + "/assets/fonts/", import.meta.url)),
			},
			{
				find: "~js",
				replacement: fileURLToPath(new URL(themeRoot + "/assets/js/", import.meta.url)),
			},
			{
				find: "~assets",
				replacement: fileURLToPath(new URL(themeRoot + "/assets/", import.meta.url)),
			}
		],
	},

	base: prod ? themeRoot + "/dist" : "/",
	mode: prod ? "production" : "development",

	build: {
		outDir:  themeRoot + "/dist",
		emptyOutDir: true,
		manifest: true,
		target: "es2021",
		rollupOptions: {
			// external: [ ...Object.keys(dependencies) ],
			input: glob
				.sync(themeRoot + "/blocks/**/*.{css,js,scss}")
				.concat(glob.sync(themeRoot + "/assets/js/single*.js"))
				.concat(glob.sync(themeRoot + "/assets/css/single*.scss"))
				.concat([
					themeRoot + "/assets/js/scripts.js",
					themeRoot + "/assets/css/style.scss",
					themeRoot + "/assets/css/single-style.scss",
					themeRoot + "/assets/css/single-editor.scss",
					themeRoot + "/assets/css/style-editor.scss",
					themeRoot + "/assets/css/_constants.scss",
					themeRoot + "/assets/css/_constants_editor.scss",
				]),
		},

		minify: true,
		write: true,
	},
	...(!prod ? {
		server: {
			cors: true,
			fs: { strict: false },

			strictPort: true,
			port: process.env.DOCKER_VITE_PORT,

			https: {
				key: fs.readFileSync("_docker/nginx/ssl/nginx-selfsigned.key"),
				cert: fs.readFileSync("_docker/nginx/ssl/nginx-selfsigned.crt"),
			},

			origin: "https://localhost:" + process.env.DOCKER_VITE_PORT
		}
	} : {}),
})
