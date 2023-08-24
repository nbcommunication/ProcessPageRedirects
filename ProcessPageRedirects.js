$(document).ready(function() {

	var id = 'ProcessPageRedirects';

	var this$1 = $('#' + id);
	if (this$1.length) {
		this$1.WireTabs({
			items: $('.Inputfields li.WireTab')
		});
	}

	var filters = $('.' + id + '-filter');
	if (filters.length) {

		var debounce = func => {
			let timer;
			return (...args) => {
				clearTimeout(timer);
				timer = setTimeout(() => { func.apply(this, args); }, 512);
			};
		};

		filters.on('keyup', debounce(e => {
			var input = e.target;
			var q = input.value.toLowerCase();
			var table = input.closest('.Inputfields').querySelector('table');

			$(table).find('tbody tr').each(function() {

				var tr = $(this);

				if (q) {
					if (tr.find('td').text().toLowerCase().indexOf(q) === -1) {
						tr.hide();
					} else {
						tr.show();
					}
				} else {
					tr.show();
				}
			});
		}));
	}
});
