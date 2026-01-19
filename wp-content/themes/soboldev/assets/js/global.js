class SobolDevThemeHeader {
	constructor() {
		this.header = document.querySelector(".theme-header");
		if (!this.header) {
			return;
		}
		this.burgerMenuButton = this.header.querySelector(".burger-menu");
		this.navigationMenu = this.header.querySelector(".navigation");
		this.menuStatus = false;

		if (!this.burgerMenuButton || !this.navigationMenu) {
			return;
		}

		this.init();
	}

	init() {
		this.setEvents();
		this.setVariables();
	}

	setEvents() {
		this.setMobileMenuEvents();
		this.setVariablesEvents();
		this.setWindowEvent();
	}

	setVariables() {
		this.setHeaderVariables();
		this.setWindowHeightVariable();
	}

	setHeaderVariables() {
		globalThis.SobolDevTheme.resizeObserver(this.header, "height", (value) => {
			globalThis.SobolDevTheme.setVariableInPx(
				document.body,
				value,
				"--header-height"
			);
		});
		globalThis.SobolDevTheme.resizeObserver(
			this.navigationMenu,
			"height",
			(value) => {
				globalThis.SobolDevTheme.setVariableInPx(
					this.navigationMenu,
					this.navigationMenu.scrollHeight,
					"--navigation-height"
				);
			}
		);

		if (!this.header.classList.contains("init")) {
			this.header.classList.add("init");
		}
	}

	setWindowHeightVariable() {
		globalThis.SobolDevTheme.setVariableInPx(
			document.documentElement,
			window.innerHeight,
			"--window-height"
		);
	}

	setMobileMenuEvents() {
		if (!this.burgerMenuButton) {
			return;
		}
		this.burgerMenuButton.addEventListener("click", (e) => {
			this.toggleMenu();
		});

		window.addEventListener("resize", (e) => {
			if (window.innerWidth > globalThis.SobolDevTheme.themeBreakpoints?.lg) {
				this.toggleMenu(true);
			}
		});

		window.addEventListener("click", (e) => {
			if (
				!this.navigationMenu.contains(e.target) &&
				this.navigationMenu.classList.contains("active") &&
				!this.burgerMenuButton.contains(e.target)
			) {
				this.toggleMenu(true);
			}
		});
	}

	setVariablesEvents() {
		window.addEventListener("resize", (e) => {
			this.setWindowHeightVariable();
		});
	}

	addHeaderClass(add) {
		this.header.classList[add ? "add" : "remove"]("scrolled");
	}

	setWindowEvent() {
		this.addHeaderClass(window.scrollY > this.header.clientHeight);
		window.addEventListener("scroll", (e) => {
			this.addHeaderClass(window.scrollY > this.header.clientHeight);
		});
	}

	toggleMenu(hide = false) {
		this.menuStatus = hide ? false : !this.menuStatus;
		this.burgerMenuButton.classList[this.menuStatus ? "add" : "remove"](
			"active"
		);
		this.navigationMenu.classList[this.menuStatus ? "add" : "remove"]("active");
		document.body.classList[this.menuStatus ? "add" : "remove"]("overlay");
		document.body.classList[this.menuStatus ? "add" : "remove"]("freeze");
	}
}

globalThis.SobolDevTheme.constructor.schedule({
	class: SobolDevThemeHeader,
	params: { name: "header" },
	prio: "low",
});
