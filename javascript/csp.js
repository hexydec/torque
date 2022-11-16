const $ = jQuery;
$(() => {
	const cls = "torque-csp__";
	$("." + cls + "control").each(function () {
		const ul = $("<ul>", {"class": cls + "list"}),
			$this = $(this);
		$this.val().split("\n").forEach(item => {
			const li = $("<li>", {"class": cls + "list-item"})
				.append($("<button>", {"class": cls + "add", text: "Add"}))
				.append($("<input>", {"class": cls + "value", value: item}))
				.append($("<button>", {"class": cls + "delete", text: "Delete"}));
			ul.append(li);
		});
		$this.after(ul);
	});
});