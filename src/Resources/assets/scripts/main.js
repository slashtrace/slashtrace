(function () {
    var MOBILE_BREAKPOINT = 1024;

    function getViewportDimensions() {
        return {
            width: Math.max(document.documentElement.clientWidth, window.innerWidth || 0),
            height: Math.max(document.documentElement.clientHeight, window.innerHeight || 0)
        };
    }

    function isVisible(element) {
        return !!(element.offsetWidth || element.offsetHeight || element.getClientRects().length);
    }

    function isNarrowViewport() {
        return getViewportDimensions().width <= MOBILE_BREAKPOINT;
    }

    function SyntaxHighlighter() {
        this.blocks = document.querySelectorAll(".frame-context");
        this.highlight();
    }

    SyntaxHighlighter.prototype.highlight = function () {
        for (var i = 0; i < this.blocks.length; i++) {
            if (isVisible(this.blocks[i])) {
                this.highlightBlock(this.blocks[i]);
            }
        }
    };

    SyntaxHighlighter.prototype.highlightBlock = function (block) {
        var pre = block.querySelector(".code pre");
        if (pre === null) {
            return;
        }

        if (pre.classList.contains("prettyprint")) {
            return;
        }

        pre.classList.add("prettyprint");
        if (pre.hasAttribute("data-start")) {
            pre.classList.add("linenums:" + pre.getAttribute("data-start"));
        } else {
            pre.classList.add("linenums");
        }

        PR.prettyPrint();

        this.highlightActiveLine(pre);
    };

    SyntaxHighlighter.prototype.highlightActiveLine = function (pre) {
        var startLine = this.getStartLine(pre);
        var activeLine = parseInt(pre.getAttribute("data-line"));
        var element = pre.querySelectorAll("ol li")[activeLine - startLine];

        element.classList.add("active");
    };

    SyntaxHighlighter.prototype.getStartLine = function (pre) {
        var element = pre.querySelector("ol li[value]");
        if (!element) {
            return 1;
        }
        return parseInt(element.getAttribute("value"));
    };

    function FrameSelector(syntaxHighlighter) {
        this.syntaxHighlighter = syntaxHighlighter;
        this.triggers = document.querySelectorAll(".stacktrace li[data-frame]");
        this.frames = document.querySelectorAll(".frame-context[data-frame]");
        this.activeIndex = -1;

        this.addListeners();
        if (!isNarrowViewport()) {
            this.selectFrame(0);
        }
    }

    FrameSelector.prototype.addListeners = function () {
        for (var i = 0; i < this.triggers.length; i++) {
            this.triggers[i].addEventListener("click", this.onClick.bind(this));
        }
    };

    FrameSelector.prototype.onClick = function (event) {
        var trigger = event.currentTarget;
        var index = this.getIndex(trigger);
        if (index === this.activeIndex) {
            if (isNarrowViewport()) {
                index = -1;
            }
        }
        this.selectFrame(index);
    };

    FrameSelector.prototype.selectFrame = function (index) {
        var className = "active";

        if (this.activeIndex > -1) {
            this.triggers[this.activeIndex].classList.remove(className);
            this.frames[this.activeIndex].classList.remove(className);
        }

        if (index > -1) {
            this.triggers[index].classList.add(className);
            this.frames[index].classList.add(className);
        }

        this.activeIndex = index;
        this.syntaxHighlighter.highlight();
    };

    FrameSelector.prototype.getIndex = function (trigger) {
        for (var i = 0; i < this.triggers.length; i++) {
            if (trigger === this.triggers[i]) {
                return i;
            }
        }
        return -1;
    };

    function TabSelector() {
        this.elements = document.querySelectorAll("ul.tabs li[data-tab-group], .tab-pane[data-tab-group]");

        var element;
        for (var i = 0; i < this.elements.length; i++) {
            element = this.elements[i];
            if (element.classList.contains("tab-pane")) {
                continue;
            }
            element.addEventListener(
                "click",
                this.onClick.bind(
                    this,
                    element.getAttribute("data-tab-group"),
                    element.getAttribute("data-tab")
                )
            );
        }
    }

    TabSelector.prototype.onClick = function (group, tab, event) {
        event.preventDefault();
        if (event.currentTarget.classList.contains("active")) {
            return;
        }

        var element;
        for (var i = 0; i < this.elements.length; i++) {
            element = this.elements[i];
            if (element.getAttribute("data-tab-group") !== group) {
                continue;
            }
            if (element.getAttribute("data-tab") === tab) {
                element.classList.add("active");
            } else {
                element.classList.remove("active");
            }
        }
    };

    function ContextExpander() {
        this.trigger = document.querySelector("a.menu");
        this.context = document.querySelector("#page > div.context");
        this.event = document.querySelector("#page > div.event");

        this.trigger.addEventListener("click", this.onTriggerClick.bind(this));
    }

    ContextExpander.prototype.onTriggerClick = function (event) {
        event.preventDefault();
        if (this.trigger.classList.contains("open")) {
            this.collapse();
        } else {
            this.expand();
        }
    };

    ContextExpander.prototype.expand = function () {
        this.trigger.classList.add("open");
        this.context.classList.add("open");
    };

    ContextExpander.prototype.collapse = function () {
        this.trigger.classList.remove("open");
        this.context.classList.remove("open");
    };

    new FrameSelector(new SyntaxHighlighter());
    new TabSelector();
    new ContextExpander();
})();
