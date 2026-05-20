(function() {
	function esc(s) {
		s = (s == null) ? '' : String(s);
		return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
	}

	function getContainer() {
		return document.getElementById('hp-filters');
	}

	function computeNextIdx(container) {
		if (typeof window.hpNextIdx === 'number') return window.hpNextIdx;
		var cards = container ? container.querySelectorAll('.hp-filter') : [];
		var max = -1;
		for (var i = 0; i < cards.length; i++) {
			var v = parseInt(cards[i].getAttribute('data-idx'), 10);
			if (!isNaN(v) && v > max) max = v;
		}
		return max + 1;
	}

	window.hpAdd = function() {
		var container = getContainer();
		if (!container) return;
		var idx = computeNextIdx(container);
		window.hpNextIdx = idx + 1;
		var L = window.HOTPOST_LANG || {};
		var card = document.createElement('div');
		card.className = 'hp-filter';
		card.setAttribute('data-idx', idx);

		var boardsHtml = '';
		(window.HOTPOST_BOARDS || []).forEach(function(b) {
			boardsHtml +=
				'<label><input type="checkbox" name="filters[' + idx + '][target_modules][]" value="' + b.module_srl + '" /> ' +
				esc(b.browser_title) + ' <span class="gray">(' + esc(b.mid) + ')</span></label>';
		});

		var html = '';
		html += '<button type="button" class="x_btn x_btn-default hp-remove" onclick="hpRemove(this)">' + esc(L.remove) + '</button>';
		html += '<h4>' + esc(L.filter) + ' #' + (idx + 1) + '</h4>';
		html += '<div class="hp-row">';
		html +=   '<label class="hp-inline"><strong>' + esc(L.name) + '</strong> <input type="text" name="filters[' + idx + '][name]" placeholder="' + esc(L.name_placeholder) + '" class="x_form-control" style="width:260px;" /></label>';
		html +=   '<label class="hp-inline"><strong>' + esc(L.query_param) + '</strong> <input type="text" name="filters[' + idx + '][query_param]" placeholder="hotpost" class="x_form-control" style="width:200px;" maxlength="32" /></label>';
		html += '</div>';
		html += '<div class="hp-row">';
		html +=   '<label class="hp-inline">' + esc(L.min_readed) + ' <input type="number" min="0" name="filters[' + idx + '][min_readed_count]" value="0" class="x_form-control" style="width:100px;" /></label>';
		html +=   '<label class="hp-inline">' + esc(L.min_voted) + ' <input type="number" min="0" name="filters[' + idx + '][min_voted_count]" value="0" class="x_form-control" style="width:100px;" /></label>';
		html +=   '<label class="hp-inline">' + esc(L.min_comment) + ' <input type="number" min="0" name="filters[' + idx + '][min_comment_count]" value="0" class="x_form-control" style="width:100px;" /></label>';
		html +=   '<label class="hp-inline">' + esc(L.period_days) + ' <input type="number" min="0" name="filters[' + idx + '][period_days]" value="0" class="x_form-control" style="width:80px;" /></label>';
		html += '</div>';
		html += '<div class="hp-row">';
		html +=   '<label class="hp-inline"><input type="radio" name="filters[' + idx + '][combine_mode]" value="and" checked="checked" /> ' + esc(L.combine_and) + '</label>';
		html +=   '<label class="hp-inline"><input type="radio" name="filters[' + idx + '][combine_mode]" value="or" /> ' + esc(L.combine_or) + '</label>';
		html += '</div>';
		html += '<div class="hp-row">';
		html +=   '<strong>' + esc(L.target_modules) + '</strong>';
		html +=   '<div class="hp-boards">' + boardsHtml + '</div>';
		html += '</div>';

		card.innerHTML = html;
		container.appendChild(card);
	};

	window.hpRemove = function(btn) {
		var card = null;
		if (btn && btn.closest) card = btn.closest('.hp-filter');
		if (!card) {
			var node = btn;
			while (node && !(node.classList && node.classList.contains('hp-filter'))) node = node.parentNode;
			card = node;
		}
		if (!card) return;
		var msg = (window.HOTPOST_LANG && window.HOTPOST_LANG.confirm_remove) || 'Remove?';
		if (!confirm(msg)) return;
		card.parentNode.removeChild(card);
	};
})();
