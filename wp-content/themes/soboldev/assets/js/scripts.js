/*
 * This file SHOULD NOT contain any imports or heavy code. This injected to the very top of the header to load as soon as possible.
 */
"use strict";
class SobolDevTheme {
	static #instance = null;
	static DEBUG = true; // this will be automatically set to false on prod
	static levels = {
		highest: 0,
		high: 1,
		domcontentloaded: 2,
		low: 3,
	};
	last_level = 0;
	static acf_inited = {};

	constructor() {
		this.init();
	}

	static getInstance() {
		if (!SobolDevTheme.#instance) {
			SobolDevTheme.#instance = new SobolDevTheme();
		}
		return SobolDevTheme.#instance;
	}

	static schedule(block) {
		if (typeof (block.prio) === "undefined" || ["highest", "high", "domcontentloaded", "low"].indexOf(block.prio.toLowerCase()) === -1) {
			SobolDevTheme.DEBUG && console.warn("Invalid priority for ", block.class.name, " block, set to default.");
			block.prio = globalThis.acf ? "highest" : "low";
		}
		(globalThis.SobolDevBlocks = globalThis.SobolDevBlocks || []).push(block);
	}

	init = () => {
		this.startTheme();
		document.addEventListener("readystatechange", () => this.startTheme(), { passive: true, });
		document.addEventListener("DOMContentLoaded", () => this.startTheme("domcontentloaded"), { passive: true });
		window.acf && window.acf.addAction("render_block_preview", (element, params) => {
			const arr = (this.constructor.acf_inited[params.name] = this.constructor.acf_inited[params.name] || [])
			if (!element || !element[0]) { return; }
			arr.push(element[0]);
		});
	}

	startTheme = (phase) => {
		const prio = ((phase) => {
			switch (phase ?? document.readyState) {
				case "loading": return "highest";
				case "interactive": return "high";
				case "complete": return "low";
			}
			return phase;
		})(phase).toLowerCase();

		globalThis.SobolDevBlocks?.forEach((block) => {
			if (!block.inited && (block?.prio.toLowerCase() === prio || (SobolDevTheme.levels[block.prio] < SobolDevTheme.levels[prio]))) {
				try {
					new block.class(block.params);
				} catch (e) {
					if (SobolDevTheme.DEBUG) { console.error("Error creating block ", block.class, " => ", e); }
				}
				block.inited = true;
			}
		});
	}

	static normalizeBlockName = (name) => {
		if (name?.match(/[^a-z0-9-_]/)) {
			name = name.replace(/[^a-z0-9-_]+/g, "-").toLowerCase(); // this should match the same from register method of /classes/BaseBlock.class.php
		}
		return name;
	}

	static throttle(func, limit) {
		let inThrottle = false;
		return function (...args) {
			if (!inThrottle) {
				func.apply(this, args);
				inThrottle = true;
				setTimeout(() => { inThrottle = false; }, limit);
			}
		};
	}

	setVariableInPx(element, propertyInPx, variable = '--element-variable') {
		element.style.setProperty(variable, propertyInPx + 'px');
	}

	static getComputedValue(property, measure = "px", element = document.documentElement) {
		const computedStyle = getComputedStyle(element);
		let value = computedStyle.getPropertyValue(property);

		if (measure === "px" && value.includes("rem")) {
			const rootFontSize = parseFloat(
				getComputedStyle(document.documentElement).fontSize
			);
			const remValue = parseFloat(value);
			value = remValue * rootFontSize;
		}

		return parseInt(value);
	}

	resizeObserver(element, property, callback) {
		if (!element) {
			return
		}
		const resizeObserver = new ResizeObserver(entries => {
			callback(entries.find(entry => entry.target === element).contentRect[property]);
		});

		resizeObserver.observe(element);

		return () => resizeObserver.disconnect();
	}
}

globalThis.SobolDevTheme = SobolDevTheme.getInstance();

globalThis.SobolDevBlockBase = class SobolDevBlockBase {
	// eslint-disable-line no-unused-vars
	_blockName;
	_instances = new Map();

	constructor(params) {
		const className = params.name;
		if (this._instances.has(className)) {
			return this._instances.get(className);
		}
		this._instances.set(className, this);
		this._blockName = globalThis.SobolDevTheme.constructor.normalizeBlockName(params.name);

		if (window.acf) {
			window.acf.addAction("render_block_preview/type=" + params.name, () => {
				this.init();
			});
		}

		if (!window.acf || globalThis.SobolDevTheme.constructor.acf_inited[params.name]) {
			this.init();
		}
	}

	init() {
		const instances = document.querySelectorAll("." + this._blockName);
		if (!instances.length) {
			throw new Error("No block instances found for : " + this._blockName);
		}

		return instances;
	}
};

