window.app = {};
let appEvent = new EventsHandler();

function showList() {
    $.ajax('/api/getParserContentList', {
        dataType: 'json',
        type: 'get',
        cache: false,
        success: response => {
            let listCont = $('.history');
            listCont.html(`<div>Адрес страницы</div>
            <div>Дата обновления</div>
            <div>Тип поиска</div>
            <div>Искомое слово</div>
            <div>Кол-во</div>
            <div>Контент</div>`);
    
            if (response['status'] == 'error') {
                listCont.html(response['message'].replace("\n", '<br />'));
                return;
            }
            
            window.app.contentList = prepareContentList(response['data']);
            response['data'].forEach(item => {
                listCont.append(`
                    <div>${item['url']}</div>
                    <div>${item['updated']}</div>
                    <div>${item['searchType']}</div>
                    <div>${item['searchQuery']}</div>
                    <div>${item['quantity']}</div>
                    <div><a href="" data-click="showDetail" data-id="${item['ID']}">Посмотреть</a></div>
                `);
            });

            appEvent.init();
        }
    });
}

function prepareContentList(contentList) {
    let prepareList = {};
    contentList.forEach(item => {
        prepareList[item['ID']] = item;
    });

    return prepareList;
}

function showDetail(id) {
    if (!(id in window.app.contentList)) {
        return;
    }

    const item = window.app.contentList[id];

    let content = item['data'].replace(/\n/g, '<br />');
    console.log('content', content);
    const searchQuery = item['searchQuery'];

    if (searchQuery.length > 0) {
        content = content.replace(
            new RegExp(searchQuery, 'g'),
            `<b>${searchQuery}</b>`
        );
    }

    $('#popup').html(content);

    $.fancybox.open({
        src  : '#popup',
        type : 'inline',
        opts : {
            touch: false,
        }
    });
}

// events
appEvent.add('send', e => {
    const formID = $(e.currentTarget).attr('data-formID');
    let formObj = $('#' + formID);
    let errorsCont = $('.errors');
    errorsCont.html('');

    $.ajax('/api/parser', {
        data: formObj.serialize(),
        dataType: 'json',
        type: 'get',
        cache: false,
        success: response => {
            if (response['status'] == 'error') {
                errorsCont.html(response['message'].replace("\n", '<br />'));
                return;
            }
            
            showList();
        }, error: e => {console.log('error')}
    });
});

appEvent.add('showDetail', e => {
    let curr = $(e.currentTarget);
    const contentId = curr.attr('data-id');
    showDetail(contentId);
});

// init
$(() => {
    appEvent.init();

    $('select[name="searchType"]').on('change', e => {
        let curr = $(e.currentTarget);
        let searchQueryField = $('input[name="searchQuery"]');

        curr.val() == 'text'
            ? searchQueryField.attr('type', 'text').attr('disabled', false)
            : searchQueryField.attr('type', 'hidden').attr('disabled', true);
    });

    showList();

    // Обработка кнопки "Назад" в браузере.
    $(window).on("popstate", function (e) {
        location.reload();
    });
});
