//(function($,mw){

    function TransPublicFunc() {
        this.fdata = null;
        this.sdata = null;
        this.$site = mw.config.get('wgSiteName');
        this.$container = $('#wiki-body');
        this.$menucontenttab = $('<div>')
            .addClass('menu-content-tab')
            .append(
                $('<span>')
                    .addClass('menu-project')
                    .text('作品'),
                $('<span>')
                    .addClass('menu-time')
                    .text('发布时间'),
                $('<span>')
                    .addClass('menu-vote')
                    .text('评分'),
                $('<span>')
                    .addClass('menu-translator')
                    .text('翻译者')
            );

        this.prePromise.apply(this, arguments);

    }

    TransPublicFunc.prototype = {

        init: function(){

            this.addPageTitle('Translation Group');
            this.addBtnGroup();
            this.addMenuGroup();

        },

        prePromise: function(){

            var siteinfoPromise = $.get('/api.php?action=query&meta=siteinfo&siprop=statistics&format=json');
            var projectPromse;
            var self = this;

            $.get('/api.php?action=query&list=allprojects&aplimit=15&apworkflow=published&format=json').done(function(data){
                var id = '';
                data.query.allprojects.forEach(function(item){
                    id+=item.tp_id+'|';
                });
                id = id.substring(0, id.length-1);
                projectPromse = $.get('/api.php?action=query&prop=projects&trids='+id+'&trprop=publicationtime|rating|translator|title&format=json');

                $.when(siteinfoPromise,projectPromse).done(function(data1,data2){
                    self.fdata = data1[0];
                    self.sdata = data2[0];
                    console.log(self.fdata,self.sdata);
                    self.init.apply(self, arguments);


                });
            });

        },


        addPageTitle: function(info){

            this.$container.append( $('<div>')
                    .addClass('transback-page-title')
                    .text(this.$site + info)
            )

        },

        addBtnGroup: function(){
            var self = this;
            var groupwrap = this.$container.append(
                $('<div>').addClass('translist-btngroup-wrap')
            ) ;

            self.createBtn($('<p>翻译作品</p><span>'+self.fdata.query.statistics.jobs+'</span>'),['translist-topgroup-btn'],groupwrap.find('.translist-btngroup-wrap'));
            self.createBtn($('<p>组成员</p><span>'+self.fdata.query.statistics.members+'</span>'),['translist-topgroup-btn'],groupwrap.find('.translist-btngroup-wrap'));
            self.createBtn($('<p>正在翻译</p><span>'+self.fdata.query.statistics.translating_work+'</span>'),['translist-topgroup-btn'],groupwrap.find('.translist-btngroup-wrap'));
            self.createBtn($('<p>正在翻译</p><span>'+self.fdata.query.statistics.translating_work+'</span>'),['translist-topgroup-btn'],groupwrap.find('.translist-btngroup-wrap'));

        },

        createBtn: function(label ,classes,wrap){

            var button = new  OO.ui.ButtonWidget({
                label: label,
                classes: classes
            });

            wrap.append(button.$element);
        },

        addMenuGroup: function(){

            var self = this;
            var menuwrap = this.$container.append(
                $('<div>').addClass('translist-menugroup-wrap')
            );

            this.createMenu(menuwrap.find('.translist-menugroup-wrap'));
        },

        getMenuTranslator: function(arr){

            var wrap = $('<div>').addClass('avatar-group-wrap'),content;
            arr.forEach(function(item){
                content = $('<a>').addClass('mw-userlink').attr({'href':'/wiki/user:'+item.name,'title':item.name,'rel':'nofollow'}).append(
                    $('<img>').attr({'src':item.avatar.ml,'data-name':item.name,'alt':'avatar'}).addClass('headimg')
                );
                wrap.append(content);
            });

            return wrap;
        },

        getMenuContent: function(){

            var content,obj = this.sdata,wrap = $('<div>').addClass('menu-content-content'),self = this;

            for(var a in obj.projects){
                content = $('<div>')
                    .addClass('menu-content-label')
                    .append(
                        $('<span>').addClass('menu-project').text(obj.projects[a].title),
                        $('<span>').addClass('menu-time').text(obj.projects[a].publicationtime),
                        $('<span>').addClass('menu-vote').append(obj.projects[a].rating),
                        $('<span>').addClass('menu-translator').append(self.getMenuTranslator(obj.projects[a].translator))
                );
                wrap.append(content);
            }

            return wrap

        },

        getMembers: function(){

            var self = this,users = '',wrap=$('<div>').addClass('hj-user-info-wrap');

            $.get('/api.php?action=query&list=allusers&augroup=member&format=json').done(function(data){
                data.query.allusers.forEach(function(item){
                    users+=item.name+'|';
                });
                users = users.substring(0, users.length-1);
                    $.get('/api.php?action=query&list=huijiusers&ususers='+users+'&usprop=avatar|level&format=json').done(function(data){
                        data.query.huijiusers.forEach(function(item){
                            var content = $('<div>').addClass('hj-user-info-group').append(
                                $('<a>').addClass('mw-userlink').attr({'href':'/wiki/user:'+item.name,'title':item.name,'rel':'nofollow'}).append(
                                    $('<img>').attr({'src':item.avatar.ml,'data-name':item.name,'alt':'avatar'}).addClass('headimg')),
                                $('<span>').addClass('hj-user-info-name').text(item.name),
                                $('<span>').addClass('hj-user-info-level icon-lv'+item.level)
                            );
                            wrap.append(content);
                        })
                    });
            });

            return wrap;

        },

        createMenu: function(wrap){

            var self = this;

            function PageOneLayout( name, config ) {
                PageOneLayout.parent.call( this, name, config );
                this.$element.append( self.$menucontenttab, self.getMenuContent() );
            }
            OO.inheritClass( PageOneLayout, OO.ui.PageLayout );
            PageOneLayout.prototype.setupOutlineItem = function () {
                this.outlineItem.setLabel( '作品发布('+self.fdata.query.statistics.jobs+')' );
            };

            function PageTwoLayout( name, config ) {
                PageTwoLayout.parent.call( this, name, config );
                this.$element.append( self.getMembers() );
            }
            OO.inheritClass( PageTwoLayout, OO.ui.PageLayout );
            PageTwoLayout.prototype.setupOutlineItem = function () {
                this.outlineItem.setLabel( '组成员('+self.fdata.query.statistics.members+')' );
            };

            var page1 = new PageOneLayout( 'one' ),
                page2 = new PageTwoLayout( 'two' );

            var booklet = new OO.ui.BookletLayout( {
                outlined: true
            } );

            booklet.addPages ( [ page1, page2 ] );
            wrap.append( booklet.$element );

        },

        constructor: TransPublicFunc
    };

$(function(){
    new TransPublicFunc();
});
//})($,mw);