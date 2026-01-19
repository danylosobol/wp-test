export class ThemeAjax {
	#action;
	#url;
	constructor(action) {
		this.#action = action;
		this.#url = SobolDevThemeVars.ajaxUrl;

		if (!this.#action || !this.#url) {
			return;
		}
	}

	async sendRequest(data, method) {
		if (!data || !method) {
			return;
		}
		const formData = this.#getFormData(data);
		return await fetch(this.#url, {
			method: method,
			credentials: "same-origin",
			body: formData,
		})
			.catch((error) => {
				throw new Error(error);
			})
			.then((response) => response.json());
	}

	#getFormData(data) {
		const formData = new FormData();
		Object.keys(data).forEach((key) =>
			formData.append(
				key,
				data[key] instanceof Object ? JSON.stringify(data[key]) : data[key]
			)
		);
		formData.append("action", this.#action);

		return formData;
	}
}
