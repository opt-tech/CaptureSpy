function SyncScroll(/* elem1, elem2, ... */) {
    this._elements = [];
    this._elementOnscroll = this._elementOnscroll.bind(this);
    this.addElement.apply(this, arguments);
}
SyncScroll.prototype = {
    enableHorizontal: true,
    enableVertical: true,
    addElement: function (/* elem1, elem2, ... */) {
        var elem, i;
        for (i = 0; i < arguments.length; i += 1) {
            elem = arguments[i];
            elem.addEventListener('scroll', this._elementOnscroll, false);
            this._elements.push(elem);
        }
    },
    removeElement: function (/* elem1, elem2, ... */) {
        var elem, i, j;
        for (i = 0; i < arguments.length; i += 1) {
            elem = arguments[i];
            elem.removeEventListener('scroll', this._elementOnscroll, false);
            j = this._elements.indexOf(elem);
            if (j >= 0) {
                this._elements.splice(j, 1);
            }
        }
    },
    _elementOnscroll: function (event) {
        var i,
            elems = this._elements,
            elem = event.target,
            x = elem.scrollLeft,
            y = elem.scrollTop;
        if (this.enableHorizontal) {
            for (i = 0; i < elems.length; i += 1) {
                elem = elems[i];
                if (elem === event.target || elem.scrollLeft === x) {
                    continue;
                }
                elem.scrollLeft = x;
                if (elem.scrollLeft !== x) {
                    elem.scrollLeft = x + x - elem.scrollLeft;
                }
            }
        }
        if (this.enableVertical) {
            for (i = 0; i < elems.length; i += 1) {
                elem = elems[i];
                if (elem === event.target || elem.scrollTop === y) {
                    continue;
                }
                elem.scrollTop = y;
                if (elem.scrollTop !== y) {
                    elem.scrollTop = y + y - elem.scrollTop;
                }
            }
        }
    },
    destroy: function () {
        this.removeElement.apply(this, this._elements);
    }
};