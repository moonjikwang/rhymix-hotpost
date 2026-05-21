(function() {
	function esc(s) {
		s = (s == null) ? '' : String(s);
		return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
	}

	function limits() {
		return window.HOTPOST_LIMITS || { period_default: 30, period_warn_max: 30 };
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

	function closestCard(el) {
		var node = el;
		while (node && !(node.classList && node.classList.contains('hp-filter'))) node = node.parentNode;
		return node;
	}

	function isPeriodInput(el) {
		return el && el.name && /\[period_days\]$/.test(el.name);
	}

	function updateWarn(input) {
		var card = closestCard(input);
		if (!card) return;
		var warn = card.querySelector('.hp-period-warn');
		if (!warn) return;
		var v = parseInt(input.value, 10);
		if (isNaN(v)) v = 0;
		var max = limits().period_warn_max;
		warn.style.display = (v === 0 || v > max) ? '' : 'none';
	}

	window.hpAdd = function() {
		var container = getContainer();
		if (!container) return;
		var idx = computeNextIdx(container);
		window.hpNextIdx = idx + 1;
		var L = window.HOTPOST_LANG || {};
		var periodDefault = limits().period_default;
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
		html +=   '<label class="hp-inline">' + esc(L.period_days) + ' <input type="number" min="0" name="filters[' + idx + '][period_days]" value="' + periodDefault + '" class="x_form-control hp-period" style="width:80px;" /></label>';
		html += '</div>';
		html += '<div class="hp-row hp-period-warn" style="color:#c0392b;display:none;">⚠ ' + esc(L.period_warning) + '</div>';
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

		var pinput = card.querySelector('.hp-period');
		if (pinput) updateWarn(pinput);
	};

	window.hpRemove = function(btn) {
		var card = closestCard(btn);
		if (!card) return;
		var msg = (window.HOTPOST_LANG && window.HOTPOST_LANG.confirm_remove) || 'Remove?';
		if (!confirm(msg)) return;
		card.parentNode.removeChild(card);
	};

	// Live warning toggle for any period field (existing or dynamically added).
	document.addEventListener('input', function(e) {
		if (isPeriodInput(e.target)) updateWarn(e.target);
	});
})();
