if (typeof window._app == 'undefined') {
    window._app = {};
}
if (!('states' in window._app)) {
    window._app['states'] = {};
}

class FormHandler
{
    constructor()
    {
    }

    static insertData(formObj, data, pref)
    {
        for (let i in data) {
            /*const target = typeof pref != 'undefined' 
                ? pref + '[' + i + ']'
                : i;*/
            let target = i;
            if (typeof pref != 'undefined') {
                target = (
                    data instanceof Array 
                    && typeof data[i] != 'object'
                )
                    ? pref + '[]'
                    : pref + '[' + i + ']';
            }
            if (typeof data[i] == 'object') {
                FormHandler.insertData(formObj, data[i], target);
            } else {
                let field = formObj.find('[type!="file"][name = "' + target + '"]');
                if (field.length == 0) {
                    continue;
                }
                if (
                    field.prop('tagName') == 'INPUT'
                    && field.attr('type') == 'checkbox'
                ) {
                    field.each((index, el) => {
                        el = $(el);
                        if (el.val() == data[i]) {
                            el.prop('checked', true);
                        }
                    });
                } else {
                    field.val(data[i]);
                    // set content to tinymce
                    if (field.attr('data-editor') !== undefined) {
                        const fieldID = field.attr('id');
                        if (fieldID !== undefined) {
                            tinyMCE.get(fieldID).setContent(data[i]);
                        }
                    }
                }
            }
        }
    }

    static clearData(formObj)
    {
        formObj[0].reset();
        formObj.find('input[type="checkbox"]').prop('checked', false);
    }

    static send(path, sendData)
    {
        sendData = sendData !== undefined ? sendData : {};
        return new Promise((resolve, reject) => {
            let sendParams = {
                data: sendData,
                type: "post",
                dataType: 'JSON',
                success: data => {
                    if (data['type'] == 'success') {
                        resolve(data);
                    } else {
                        reject(('message' in data ? data['message'] : 'Произошла ошибка'));
                    }
                },
                error: error => reject(error)
            };
            if (sendData instanceof FormData) {
                sendParams['processData'] = false;
                sendParams['contentType'] = false;
            }
            $.ajax(path, sendParams);
        });
    }
}

class TextHandler
{
    constructor()
    {
    }

    static replaceInText(txt, data, pref)
    {
        for (let i in data) {
            const target = typeof pref != 'undefined' 
                ? pref + '[' + i + ']'
                : i;
            if (typeof data[i] == 'object' && data[i] !== null) {
                txt = TextHandler.replaceInText(txt, data[i], target);
            } else {
                txt = TextHandler.replaceAll(target, data[i], txt);
            }
        }
        return txt;
    }

    static replaceAll(target, repl, txt)
    {
        const escapeRegExp = str => str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return txt.replace(new RegExp(escapeRegExp('{' + target + '}'), 'g'), repl);
    }
}

class EventsHandler
{
    constructor(pref)
    {
        this.pref = pref !== undefined ? String(pref) : "click";
        this.events = {};
        this.states = {};
    }

    add(name, cb)
    {
        this.events[name] = cb;
    }

    init(parent)
    {
        // init click events
        if (parent === undefined) {
            parent = $("body");
        }
        parent.find('[data-'+this.pref+']').unbind("click").click(e => {
            e.preventDefault();
            const eventName = $(e.currentTarget).attr('data-' + this.pref);
            console.log("click:", eventName);
            if (eventName in this.events) {
                this.events[eventName](e);
                this.states[eventName] = true;
            }
            else {
                console.log("click error:", eventName);
            }
        });
    }

    isRunning(eventName)
    {
        return eventName in this.states;
    }

    clear(eventName)
    {
        return delete this.states[eventName];
    }
}

class PageHandler
{
    static getPageHeight()
    {
        return Math.max(
            document.body.scrollHeight, document.documentElement.scrollHeight,
            document.body.offsetHeight, document.documentElement.offsetHeight,
            document.body.clientHeight, document.documentElement.clientHeight
        );
    }

    static scroll(target, top, duration)
    {
        const pageHeight = PageHandler.getPageHeight();
        top = top !== undefined ? parseInt(top) : 0;
        duration = duration !== undefined ? parseInt(duration) : 500;
        if (typeof target != 'object') {
            target =  $(target);
        }

        const headerFixedHeight = () => {
            let fixedHeader = $('header.fixed-header, header.header-fix');
            if (
                fixedHeader.length > 0
                && fixedHeader.is(":visible")
                && fixedHeader.css('position') == 'fixed'
            ) {
                return fixedHeader.outerHeight();
            }
            return 0;
        };

        if (target.length > 0) {

            if (target.closest('.fr-pop').length > 0) {
                //$('.fancybox-slide').stop().animate({ scrollTop: target[0].offsetTop }, duration);
                target[0].scrollIntoView({behavior: 'smooth'});
            } else {
                let targetTop = target.offset().top + top;
                targetTop -= headerFixedHeight();
                $('html,body').stop().animate({ scrollTop: targetTop }, duration, null, () => {
                    // Корректирующий скролл. Если в процессе скролла страница меняет высоту.
                    if (pageHeight != PageHandler.getPageHeight()) {
                        if (typeof target[0].scrollIntoView == 'function') {
                            target[0].scrollIntoView({behavior: 'auto'});
                        } else {
                            PageHandler.scroll(target, top, duration);
                        }
                    }
                });
            }
        }
    }

    static jumpTo(target, top)
    {
        PageHandler.scroll(target, top, 100);
    }

    static runAfterLoad(selector)
    {
        return new Promise((resolve, reject) => {
            let items = $(selector);
            let itemsCnt = items.length;
            items.one('load', e => {
                itemsCnt -= 1;
                if (itemsCnt == 0) {
                    resolve();
                }
            }).each((index, el) => {
                if(el.complete) {
                    $(el).trigger('load');
                }
            });
        });
    }

    static runAfterVisible(selector, cb)
    {
        const stateKey = 'checkVisible_' + selector;
        const checkVisible = () => {
            if (stateKey in window._app['states']) {
                return false;
            }
            window._app['states'][stateKey] = true;
            setTimeout(() => {
                /*
                let item = $(selector);
                const windowBottom = window.pageYOffset + document.documentElement.clientHeight;
                let itemPosition = item.offset();
                if (windowBottom > itemPosition.top) {
                */
                if (PageHandler.checkVisible(selector)) {
                    //cb(item);
                    cb($(selector));
                    document.removeEventListener("scroll", checkVisible);
                    window.removeEventListener("resize", checkVisible);
                }
                delete window._app['states'][stateKey];
            }, 200);
        };
        //
        document.addEventListener('scroll', checkVisible);
        window.addEventListener('resize', checkVisible);
        checkVisible();
    }

    static runAfterFontsLoaded(cb)
    {
        if (document.fonts !== undefined) {
            document.fonts.ready.then(cb);
        } else {
            setTimeout(cb, 200);
        }
    }

    static checkVisible(selector)
    {
        let item = $(selector);
        let isVisible = false;
        if (
            (
                typeof item[0] !== 'undefined'
                && item[0].getBoundingClientRect().top <= window.innerHeight
                && item[0].getBoundingClientRect().bottom >= 0
            )
            && getComputedStyle(item[0]).display != "none"
        ) {
            isVisible = true;
        }
        return isVisible;
    }

    // Проверяет есть ли у элемента скрытый контент
    static hasOverflowContent(el)
    {
        return (el.clientHeight < el.scrollHeight)
            || (el.clientWidth < el.scrollWidth);
    }
}

class OnEventHandler
{
    constructor()
    {
        this.preparies = {};
    }

    add(name, cb)
    {
        if (!(name in this.preparies)) {
            this.preparies[name] = [];
        }
        this.preparies[name].push(cb);
    }

    run(name, params)
    {
        if (name in this.preparies) {
            this.preparies[name].forEach(cb => cb(params));
        }
    }

    clear(name)
    {
        if (name in this.preparies) {
            this.preparies[name] = [];
        }
    }
}

// timer

// constructor
class TimerHandler
{
    constructor(remain, elementID)
    {
        this.dateEnd = new Date();
        remain = parseInt(remain);
        remain += 10; // add 10 sec for that to exactly ending after server round reset
        remain *= 1000; // convert to milliseconds
        this.dateEnd.setTime(this.dateEnd.getTime() + remain);
        this.elementID = elementID;
    }

    // methods
    run()
    {
        const currentDate = new Date();
        let tmx = this.dateEnd.getTime() - currentDate.getTime(); // get remain milliseconds
        const dayLength = 86400000; // milliseconds in day
        
        if (tmx <= 1000) {
            document.getElementById(this.elementID).innerHTML = "00:00:00";
            window.location.reload();
        }

        let remainTime = '';
        if (tmx > dayLength) {
            const days = Math.floor(tmx / dayLength);
            remainTime += days;
            remainTime += ' ' + this.getDaysName(remainTime) + ' ';
            tmx -= days * dayLength;
        }

        let seconds = Math.floor(tmx / 1000);
        let hours = Math.floor(seconds / 3600);
        if (hours > 0) {
            seconds -= hours * 3600;
        }
        let minutes = Math.floor(seconds / 60);
        if (minutes > 0) {
            seconds -= minutes * 60;
        }
        remainTime += this.addZero(hours) + ":" + this.addZero(minutes) + ":" + this.addZero(seconds);

        document.getElementById(this.elementID).innerHTML = remainTime;
        setTimeout(() => this.run(), 1000);
    }

    addZero(number)
    {
        return number < 10 ? "0" + String(number) : String(number);
    }

    getDaysName(days)
    {
        const lastNum = parseInt(days.toString().substr(-1));
        const lastTwo = parseInt(days.toString().substr(-2));
        let result = '';
        if (lastNum == 1 && lastTwo != 11) {
            result = "день";
        } else if (lastNum < 5 && !(lastTwo > 10 && lastTwo < 20)) {
            result = "дня";
        } else {
            result = "дней";
        }
        return result;
    };
}
