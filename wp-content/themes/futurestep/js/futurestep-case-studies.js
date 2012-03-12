var utilities = {
    toSlug : function(before) {

        return before.replace(/[^a-z0-9]/gi, '-');
    }

};

var templates = {
    filterlist :{
        template    :   ['<div>',
            '   <h1></h1>',
            '   <ul></ul>',
            '</div>'].join(''),
        fn          : function(title, type, items, csref) {
            var $el = $(this.template);
            $('h1', $el).text(title);

            var $ul = $('ul', $el);

            for (var i = 0; i < items.length; i++) {
                (function(_i) {
                    var $li = $(['<li><a href="#!/casestudies/', utilities.toSlug(items[_i].title.toLowerCase()),'">',items[_i].title,'</li>'].join(''));
                    $li.children('a').click(function(e) {
                        e.preventDefault();
                        csref.renderFilteredClients(type, items[_i].id);
                        return false;
                    });
                    $ul.append($li);
                })(i);
            }

            return $el;
        }
    },

    clientlist : {
        template : [   '<section class="logos">',
            '   <ul>',
            '   </ul>',
            '</section>'].join(''),
        fn      : function(clients, csref) {

            var $el = $(this.template);
            var $ul = $('ul', $el);

            for (var i = 0; i < clients.length; i++) {
                (function(_i) {
                    var $li = $('<li><a href="#!/case-study/"><img src="" alt="Company As logo"></a></li>');
                    $li.attr('data-id', clients[_i].id)
                    $li.children('a')
                        .click(
                        function(e) {
                            e.preventDefault();

                            return false;
                        }).
                        attr('href','#!/case-study/' + utilities.toSlug(clients[_i].title.toLowerCase())).
                        children('img').attr('src', clients[_i].image).attr('alt', clients[_i].title + ' logo');

                    $ul.append($li);
                })(i);
            }

            return $el;
        }
    }
};

var CS = function(data) {
    this.data = data;
};

CS.prototype.getFilterListEl = function(property, title) {
    var node = this.data[property];

    return templates['filterlist'].fn(title, property, node, this);
};

CS.prototype.getClientsListEl = function(filter, value) {
    var node = $.extend([], this.data['client']);

    if (!filter) {
        return templates['clientlist'].fn(node, this);
    }
    else{
        node = this.filterClients(node, filter, value);

        return templates['clientlist'].fn(node, this);
    }
};

CS.prototype.filterClients = function(clients, filter, value){
    var newclients = [];
    for(var i = 0; i < clients.length; i++){
        if(clients[i][filter].indexOf(value) > -1){
            newclients.push(clients[i]);
        }
    }

    return newclients;
    //this.filterClients(clients, filter, value);
};

CS.prototype.renderFilteredClients = function(filter, value) {
    var $parent = $('.clients .swap').html(null);
    var $clients = this.getClientsListEl(filter, value);

    $parent.append($clients);
    $('.logos ul').quicksand($('li', $parent));
};


$(function() {

    var Helpers = {
        renderFilter : function(cs, filtersContainer, id, title) {
            var $filter = cs.getFilterListEl(id, title);
            $(filtersContainer).append($filter);
        },
        renderClients : function(cs, clientsContainer, filter, value) {
            var $clients = cs.getClientsListEl(filter, value);
            $(clientsContainer).append($clients);
        }
    };

    console.log('futurestep-case-studies.js loaded');

    $.getJSON('js/data/case-study-data.json',

        function(dset) {

            var cs = new CS(dset);

            //create filters
            Helpers.renderFilter(cs, '.filters .container', 'location', 'Geography');
            Helpers.renderFilter(cs, '.filters .container', 'sector', 'Sectors');
            Helpers.renderFilter(cs, '.filters .container', 'service', 'Services');

            //create clients
            Helpers.renderClients(cs, '.clients .logos');

        });
});