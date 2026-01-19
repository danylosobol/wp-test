import { ThemeAjax } from "~js/modules/ajax";
import { name } from "./block.json";

("use strict");

class LoadMore {
	#section;
	#ajax;
	#button;
	#currentPage = 1;
	#perPage = 3;
	#sort = "ASC";
	#wrapper;
	#sortButton;
	constructor(scope) {
		if (!(scope instanceof HTMLElement)) {
			return;
		}

		this.#section = scope;
		this.#button = scope.querySelector(".load-more");
		this.#ajax = new ThemeAjax("load_properties");
		this.#sortButton = scope.querySelector(".sort-button");
		this.#wrapper = scope.querySelector(".blocks");

		this.#init();
	}

	#init() {
		this.#setEventOnLoadMoreButton();
		this.#setEventOnSortButton();
	}

	#setEventOnLoadMoreButton() {
		if (!(this.#button instanceof HTMLButtonElement)) {
			return;
		}

		this.#perPage = Number(this.#button.dataset.perPage);

		this.#button.addEventListener("click", async (e) => {
			this.#currentPage++;
			await this.#ajax
				.sendRequest(
					{
						current_page: this.#currentPage,
						sort: this.#sort,
						per_page: this.#perPage,
					},
					"POST",
				)
				.then((response) => {
					this.#setResult(response, true);
				});
		});
	}

	#setEventOnSortButton() {
		if (!(this.#sortButton instanceof HTMLButtonElement)) {
			return;
		}

		this.#sortButton.addEventListener("click", async (e) => {
			this.#currentPage = 1;
			this.#sort = this.#sort === "DESC" ? "ASC" : "DESC";
			const text = SobolDevThemeVars?.i18n?.sort_button[this.#sort];
			if (text) {
				this.#sortButton.innerText = text;
			}

			await this.#ajax
				.sendRequest(
					{
						current_page: this.#currentPage,
						sort: this.#sort,
						per_page: this.#perPage,
					},
					"POST",
				)
				.then((response) => {
					this.#setResult(response, false);
				});
		});
	}

	#setResult(response, isLoadingMore = false) {
		if (!response.data) {
			return;
		}

		if (this.#button instanceof HTMLButtonElement) {
			if (response.data.current_page >= response.data.total_pages) {
				this.#button.disabled = true;
			} else {
				this.#button.removeAttribute("disabled");
			}
		}

		if (this.#wrapper instanceof HTMLElement && response.data.html.length > 0) {
			if (isLoadingMore) {
				this.#wrapper.innerHTML += response.data.html.join("");
			} else {
				this.#wrapper.innerHTML = response.data.html.join("");
			}
		}
	}
}

class Properties extends SobolDevBlockBase {
	init() {
		const instances = super.init();
		instances?.forEach((block) => {
			new LoadMore(block);
		});
	}
}

SobolDevTheme.constructor.schedule({
	class: Properties,
	params: { name },
	prio: "low",
});
