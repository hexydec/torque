const $ = jQuery;
$(() => {
	const cls = "torque-csp__",

		// function to add a row to CSP
		add = (name, value = "") => {
			return $("<li>", {"class": cls + "list-item"})
				.append($("<button>", {
					"class": cls + "add dashicons dashicons-insert",
					title: "Add item to list",
					text: "Add"
				}))
				.append($("<input>", {
					"class": cls + "value",
					value: value,
					name: name
				}))
				.append($("<button>", {
					"class": cls + "delete dashicons dashicons-remove", 
					title: "Remove item from list", 
					text: "Delete"
				}));
		};

	// bind buttons
	$("." + cls + "list")

		// add row
		.on("click", "." + cls + "add", function (e) {
			const parent = $(this).parent();
			parent.after(add($("." + cls + "value", parent.parent()).attr("name")));
			e.preventDefault();
		})

		// delete row
		.on("click", "." + cls + "delete", function () {
			const parent = $(this).parent(),
				siblings = parent.siblings().not(parent);
			parent.remove();
			if (siblings.length === 1) {
				$("." + cls + "delete", siblings).remove();
			}
		});

	// add recommendation
	$("." + cls + "recommendations-add").on("click", function (e) {
		const $this = $(this),
			value = $this.text().trim();
			parent = $this.closest("." + cls + "recommendations").parent().children("." + cls + "list"),
			children = parent.children();

		// check value is not already set
		let found = false;
		children.each(function () {
			if ($("." + cls + "value", this).val() === value) {
				found = true;
				return false;
			}
		});

		// add value
		if (!found) {
			parent.append(add($("." + cls + "value", parent).attr("name"), value));
		}
		e.preventDefault();
	});
});