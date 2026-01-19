import { name } from "./block.json";

("use strict");

class Numbers {
	constructor(scope) {
		if (!scope) {
			return;
		}

		this.section = scope;
		this.numbersParentElement = this.section.querySelector(".blocks");
		this.numbers = this.section.querySelectorAll(".number-element");

		if (this.numbers.length === 0 || !this.numbersParentElement) {
			return;
		}

		this.init();
	}

	init() {
		this.setEventOnParentElement();
	}

	setEventOnParentElement() {
		this.numbersParentElement.addEventListener("in-viewport", (e) => {
			this.numbers.forEach((number) => {
				this.animateNumber(number, 0, parseInt(number.innerText), 2000);
			});
		});
	}

	animateNumber(element, start, end, duration) {
		const startTime = Date.now();
		const interval = 1000 / 60;
		const self = this;

		function step() {
			const currentTime = Date.now();
			const elapsedTime = currentTime - startTime;
			const progress = Math.min(elapsedTime / duration, 1);

			const currentValue = start + (end - start) * progress;

			self.updateNumber(element, currentValue.toFixed(1));

			if (progress < 1) {
				setTimeout(step, interval);
			}
		}

		step();
	}

	updateNumber(element, currentValue) {
		element.textContent = this.formatNumberWithCommas(parseInt(currentValue));
	}

	formatNumberWithCommas(number) {
		return number.toLocaleString("en-US", {
			minimumFractionDigits: 0,
			maximumFractionDigits: 0,
		});
	}
}

class Hero extends SobolDevBlockBase {
	init() {
		const instances = super.init();
		instances?.forEach((block) => {
			new Numbers(block);
		});
	}
}

SobolDevTheme.constructor.schedule({
	class: Hero,
	params: { name },
	prio: "low",
});
