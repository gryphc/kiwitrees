(function (c) {
	var d = c.ui.autocomplete.prototype,
		f = d._initSource;
	c.extend(d, {
		_initSource: function () {
			this.options.html && c.isArray(this.options.source) ? this.source = function (b, i) {
				var h;
				h = this.options.source;
				var g = RegExp(c.ui.autocomplete.escapeRegex(b.term), "i");
				h = c.grep(h, function (e) {
					return g.test(c("<div>")
						.html(e.label || e.value || e)
						.text())
				});
				i(h)
			} : f.call(this)
		},
		_renderItem: function (b, e) {
			return c("<li></li>")
				.data("item.autocomplete", e)
				.append(c("<a></a>")[this.options.html ? "html" : "text"](e.label))
				.appendTo(b)
		}
	})
})(jQuery);
jQuery("#spouseid, input[id*=pid], input[id*=PID], input[id^=gedcomid], input[id^=rootid], input[id$=ROOT_ID], input[name^=FATHER], input[name^=MOTHER], input[name^=CHIL]")
	.autocomplete({
		source: "autocomplete.php?field=INDI",
		html: !0
	});
jQuery(".ASSO")
	.autocomplete({
		source: function (c, d) {
			jQuery.getJSON("autocomplete.php?field=ASSO", {
				term: c.term,
				pid: jQuery("input[name=pid]")
					.val(),
				event_date: jQuery("input[id*=_DATE]")
					.val()
			}, d)
		},
		html: !0
	});
jQuery(".FAM, input[id*=famid], input[id*=FAMC], #famid")
	.autocomplete({
		source: "autocomplete.php?field=FAM",
		html: !0
	});
jQuery(".NOTE, .SHARED_NOTE")
	.autocomplete({
		source: "autocomplete.php?field=NOTE",
		html: !0
	});
jQuery(".SOUR, input[id*=sid]")
	.autocomplete({
		source: "autocomplete.php?field=SOUR"
	});
jQuery(".PAGE")
	.autocomplete({
		source: function (c, d) {
			jQuery.getJSON("autocomplete.php?field=SOUR_PAGE", {
				term: c.term,
				sid: jQuery("input[class^=SOUR]")
					.val()
			}, d)
		}
	});
jQuery("#TITL")
	.autocomplete({
		source: "autocomplete.php?field=SOUR_TITL"
	});
jQuery(".REPO, #REPO")
	.autocomplete({
		source: "autocomplete.php?field=REPO"
	});
jQuery("#REPO_NAME")
	.autocomplete({
		source: "autocomplete.php?field=REPO_NAME"
	});
jQuery(".OBJE, #OBJE, #mediaid, #filter")
	.autocomplete({
		source: "autocomplete.php?field=OBJE",
		html: !0
	});
jQuery("input[id$=xref], input[name^=gid], #cart_item_id")
	.autocomplete({
		source: "autocomplete.php?field=IFSRO",
		html: !0
	});
jQuery(".PLAC, #place, input[name=place], input[id=place], input[name*=PLACS], input[name*=PLAC3], input[name^=PLAC], input[name$=PLAC]")
	.autocomplete({
		source: "autocomplete.php?field=PLAC"
	});
jQuery("input[name=place2], input[id=birthplace], input[id=marrplace], input[id=deathplace], input[id=bdmplace]")
	.autocomplete({
		source: "autocomplete.php?field=PLAC2"
	});
jQuery("input[id*=_CEME]")
	.autocomplete({
		source: "autocomplete.php?field=CEME"
	});
jQuery("#GIVN, input[name*=GIVN], input[name*=firstname]")
	.autocomplete({
		source: "autocomplete.php?field=GIVN"
	});
jQuery("#SURN, input[name*=SURN], input[name*=lastname], #NAME, input[id=name]")
	.autocomplete({
		source: "autocomplete.php?field=SURN"
	});