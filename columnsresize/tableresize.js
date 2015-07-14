var ColumsResize = function(config) {
    var cfg = config || console.log('ColumsResize not init: config is undefined'),
        selfProp = {
            idPfx : '#',
            clPfx : '.',
            dataAttr : 'data-cl-resize',
            draggableLineSfx : '-resize-line',
            resizeLineSfx : '-resize-table',
            columnsDataSfx : 'column-',
            minWidthProp : 'minWidth',
            cfxClass : '_column-resize-clearfix',
            maxStringLength : 4
        },
        parentWidth,
        localData;
    this.selfProp = selfProp;

    localData = false;//this.get().localStorage(cfg.table);

    if (localData) {
        cfg = localData;
        cfg.tableRow = $(selfProp.idPfx + cfg.table).find('tr');
        cfg.tableCells = $(selfProp.idPfx  + cfg.table).find('td', 'th');
        cfg.tableColumnsArray = this.get().tableColumnsArray(cfg.tableCells, cfg.cellsInColumn);
        this.set().dataAttr(cfg.tableColumnsArray, selfProp.dataAttr);
        cfg.tableColumnsDataObj = this.get().tableColumnsData(cfg.tableColumnsArray, cfg.tableColumnsWidthArray, selfProp.dataAttr, cfg.tableColumnsDataObj);
        this.set()._cellsWidth(cfg.tableColumnsDataObj);
    } else {
        if ( cfg.table ) {
            cfg.tableW = $(selfProp.idPfx + cfg.table).width();
            cfg.tableH = $(selfProp.idPfx + cfg.table).height();
            cfg.tableRow = $(selfProp.idPfx + cfg.table).find('tr');
            cfg.tableCells = $(selfProp.idPfx  + cfg.table).find('td', 'th');
            cfg.tableBorderWidth = cfg.tableCells.css('border-width');
            cfg.cellsInColumn = cfg.tableCells.length / cfg.tableRow.length;
            cfg.sufixDraggableLine = '-resize-line';
            cfg.tableColumnsArray = this.get().tableColumnsArray(cfg.tableCells, cfg.cellsInColumn);
            cfg.tableColumnsWidthArray = this.get().tableColumnsWidth(cfg.tableColumnsArray);
            this.set().dataAttr(cfg.tableColumnsArray, selfProp.dataAttr);
            cfg.tableColumnsDataObj = this.get().tableColumnsData(cfg.tableColumnsArray, cfg.tableColumnsWidthArray, selfProp.dataAttr);
            parentWidth = $(selfProp.idPfx + cfg.table).parent().width();
            cfg.parentProportion = cfg.tableW === parentWidth ? 100 : cfg.tableW/parentWidth*100;
        }
    }
    this.config = cfg;
};
/*
 * eventListener: bind all events
 * */
ColumsResize.prototype.eventListener = function(){
    var t = this;

    $(window).resize(function(){
        t._handlers().tableWindowResize();
        if (t.check()._resize.tableHeight()){
            t._handlers().draggableDivsResize();
            t._handlers().tableDivsResize();
        }
    });
};

ColumsResize.prototype.check = function(){
    var cfg = this.config,
        sp = this.selfProp,
        table = $(sp.idPfx+cfg.table);
    return {
        _resize : {
            tableHeight : function(){
                return cfg.tableH !== table.height();
            },
            tableWidth : function(){
                return cfg.tableW !== table.width();
            }
        }
    }
};

ColumsResize.prototype._handlers = function(){
    var cfg = this.config,
        sp = this.selfProp;
    return {
        draggableDivsResize : function(){
            var elem = $(sp.clPfx + cfg.table+cfg.sufixDraggableLine),
                tableHeight = $(sp.idPfx + cfg.table).height();

            elem.height(tableHeight);
            cfg.tableH = tableHeight;
            return tableHeight;
        },
        tableDivsResize : function(){
            var elem = $(sp.clPfx + cfg.table+sp.resizeLineSfx),
                tableHeight = $(sp.idPfx + cfg.table).height();

            elem.height(tableHeight);
            cfg.tableH = tableHeight;
            return tableHeight;
        },
        tableWindowResize : function(){
            var table = $(sp.idPfx +cfg.table),
                parentTable = table.parent(),
                newWidth;

            newWidth = parentTable.width()/100*cfg.parentProportion;
            cfg.tableW = newWidth;
            table.width(newWidth);
        }
    }
};
/*
 * init: method for initialize application
 * */
ColumsResize.prototype.init = function() {
    var sp = this.selfProp, //sp - self property (generate when constructor start);
        cfg = this.config, //cfg - config;
        cellsMaxHeight;

    $(sp.idPfx + cfg.table).width(cfg.tableW);
    this.wrapCellData(cfg.tableCells);
    cellsMaxHeight = this.get().cellsMaxHeight(cfg.tableRow);
    this.set().cellsMaxHeight(cfg.tableRow, cellsMaxHeight, sp.maxStringLength);
    this.set().dataAttr(cfg.tableColumnsArray, cfg.dataAttr);
    this.createDraggableTemplate(cfg.tableColumnsArray, cfg.tableBorderWidth);
    this.createResizeTemplate(cfg.tableColumnsArray);
    this.minWidth(cfg.tableColumnsDataObj, cfg.cellsInColumn);
    this.draggableInit(cfg.tableColumnsDataObj);
    this.resizeTableInit(cfg.tableColumnsDataObj);
    this.eventListener();
    $(sp.idPfx + cfg.table).css('visibility', 'visible');
};
/*
 * minWidth: method for get min width columns value ( pushed min width to columns data object )
 * arguments: object with data about columns, columns length
 * */
ColumsResize.prototype.minWidth = function(columnsObj, columnsLength) {
    var i = 0,
        co = columnsObj || this.config.tableColumnsDataObj || console.log('CR.minWidth: columnsObj is undefined'),
        cl = typeof columnsLength === "number" ? columnsLength : console.log('CR.minWidth: columnsLength is not number'),
        curCol,
        curWidth,
        curEl,
        cfg = this.config,
        sp = this.selfProp; //sp - self property (generate when constructor start);

    cfg.tableMW = 0;

    for (i; i<cl; i++) {
        curCol = sp.columnsDataSfx + i;
        curEl = co[curCol].elements;
        curWidth = curEl.width();
        curEl.width(1);
        curMinWidth = curEl.width();
        cfg.tableMW += curMinWidth;
        co[curCol][sp.minWidthProp] = curMinWidth;
        curEl.width(curWidth);
    }
};
/*
 * draggableInit: method for init draggable events and make drag
 * arguments: object with data about columns
 * */
ColumsResize.prototype.draggableInit = function( columnsObj ){
    var curEl = $(),
        co = columnsObj || this.config.tableColumnsDataObj || console.log('CR.draggableInit: columnsObj is undefined'),
        sp = this.selfProp,
        cfg = this.config,
        t = this,
        clsPfx = '.',
        curElCurPos,
        curElObj = {},
        depsElObj = {},
        depsEl,
        resizeColumnsMousemove = function(event){
            var width = event.pageX - curElCurPos;
            if (curEl.hasClass(cfg.table+'draggable') && curElObj.minWidth < curElObj.width + width && depsElObj.minWidth < depsElObj.width - width) {
                curElObj.width = curElObj.width + width;
                depsElObj.width = depsElObj.width - width;
                curElCurPos = event.pageX;
                t.set()._cellsWidth(co);
            }
        },
        resizeColumnsMouseup = function(){
            $('body')
                .removeClass('_column-resize-change-cursor')
                .unbind('mousemove', resizeColumnsMousemove);
            $(window).unbind('mouseup', resizeColumnsMouseup);
            curEl.removeClass(cfg.table+'draggable');
            if ( t.check()._resize.tableHeight() ) {
                t._handlers().draggableDivsResize();
                t._handlers().tableDivsResize();
            }
            t.set().localStorage(cfg);
        };

    $(clsPfx + cfg.table + cfg.sufixDraggableLine).mousedown(function(event){
        var curElAttr,
            depElAttrNum,
            depsElAttr;

        curEl = $(this).parent();
        curElAttr = curEl.attr(sp.dataAttr);
        depElAttrNum = Number(curElAttr.match(/\d+/)) + 1;
        depsElAttr= 'column-'+depElAttrNum;
        curElObj = co[curElAttr];
        depsElObj = co[depsElAttr];
        depsEl = depsElObj.elements;
        curEl.addClass(cfg.table+'draggable');
        curElCurPos = event.pageX;
        $('body')
            .addClass('_column-resize-change-cursor')
            .bind('mousemove', resizeColumnsMousemove);
        $(window).bind('mouseup', resizeColumnsMouseup)
    });
};

ColumsResize.prototype.resizeTableInit = function( columnsObj ){
    var t = this,
        sp = t.selfProp,
        cfg = t.config,
        curEl = $(sp.idPfx + cfg.table),
        co = columnsObj || this.config.tableColumnsDataObj || console.log('CR.draggableInit: columnsObj is undefined'),
        curElCurPos,
        curRange,
        floatSide;
    resizeTableMousemove = function(event){
        var width = event.pageX - curElCurPos,
            newWidth,
            newColumnWidth;
        if (curEl.hasClass(cfg.table+'resize-table') && cfg.tableMW < cfg.tableW + width && cfg.tableMW < cfg.tableW - width) {
            newWidth = curRange === 'left' ? cfg.tableW - width : cfg.tableW + width;
            for (el in co) {
                newColumnWidth = newWidth*(co[el].width / cfg.tableW);
                co[el].width = newColumnWidth;
                co[el].elements.width(newColumnWidth);
            }
            cfg.tableW = newWidth;
            curEl.width(newWidth)
            curElCurPos = event.pageX;
        }
    },
        resizeTableMouseup = function(){
            $('body')
                .removeClass('_column-resize-change-cursor')
                .unbind('mousemove', resizeTableMousemove);
            $(window).unbind('mousemove', resizeTableMouseup);
            cfg.parentProportion = cfg.tableW/curEl.parent().width()*100;
            curEl.removeClass(cfg.table+'resize-table');
            if ( t.check()._resize.tableHeight() ) {
                t._handlers().draggableDivsResize();
                t._handlers().tableDivsResize();
            }
            t.set().localStorage(cfg);
        };
    $(sp.clPfx + cfg.table + sp.resizeLineSfx).mousedown(function(event){
        curEl.addClass(cfg.table+'resize-table');
        curElCurPos = event.pageX;
        curRange = parseInt($(this).css('left')) === 0 ? 'left' : 'right';
        floatSide = curRange === 'left' ? 'right' : 'left';
        curEl
            .css('float', floatSide)
            .parent().addClass(sp.cfxClass);

        $('body')
            .addClass('_column-resize-change-cursor')
            .bind('mousemove', resizeTableMousemove);
        $(window).bind('mouseup', resizeTableMouseup);
    })
};

ColumsResize.prototype.get = function() {
    var that = this;
    return {
        /*
         * arguments : cells elements array, cells length in column
         * return : array columns with array cells in current columns
         * */
        tableColumnsArray : function( cellsArray, cellsInColumn){
            var i = 0,
                array = [],
                length = cellsArray.length,
                innerCounter = 0;

            for (i; i< length; i++) {
                i !== 0 ? innerCounter++ : innerCounter = 0;
                if (innerCounter >= cellsInColumn) {
                    innerCounter = 0;
                }
                if (i < cellsInColumn) {
                    array[innerCounter] = [];
                }
                array[innerCounter].push(cellsArray[i])
            }
            return array;
        },
        /*
         * arguments : array with columns elements
         * return : array with columns width
         * */
        tableColumnsWidth : function( tableColumnsArray ) {
            var i = 0,
                array = [],
                lengthColumns = tableColumnsArray.length;

            for (i; i<lengthColumns; i++) {
                array[i] = $(tableColumnsArray[i][0]).width();
            }
            return array;
        },
        /*
         * arguments : array with columns elements and array with columns elements width
         * return : object with data-attr and property: width
         * */
        tableColumnsData : function( tableColumnsArray, tableColumnsWidth, dataAttr, storage) {
            var i = 0,
                sp = that.selfProp,
                length = tableColumnsArray.length,
                columnsDataPrx = sp.columnsDataSfx,
                obj = {},
                da = dataAttr || this.selfProp.dataAttr,
                concat;

            for (i; i<length; i++) {
                concat = columnsDataPrx + i;
                obj[concat] = {
                    width : !storage ? tableColumnsWidth[i] : storage[concat].width,
                    elements : $('['+da+'='+columnsDataPrx+i+']')
                }
            }
            return obj;
        },
        /*
         * arguments : rows array
         * return : array with max height property in row
         * */
        cellsMaxHeight : function(rows){
            var i = 0,
                curEl,
                length = rows.length,
                cells,
                cellsLength,
                innerCounter,
                height = [];

            for (i; i<length; i++) {
                cells = $(rows[i]).children('td', 'th');
                cellsLength = cells.length;
                innerCounter = 0;
                height[i] = 0;
                for (innerCounter; innerCounter<cellsLength; innerCounter++) {
                    curEl = $(cells[innerCounter]).children('div');
                    curEl.css('white-space', 'nowrap');
                    height[i] = curEl.height() > height[i] ? curEl.height() : height[i];
                    curEl.css('white-space', 'normal');
                }
            }
            return height;
        },
        localStorage : function(key){
            var key = key + '=' + window.location.href;

            return JSON.parse(localStorage.getItem(key));
        }
    }
};

ColumsResize.prototype.set = function() {
    var that = this;
    return {
        /*
         * set data attribute to cells in table
         * arguments : array with cells in columns, attribute name
         * */
        dataAttr : function( columnsArray, attrName ) {
            var i = 0,
                sp = that.selfProp,
                columnsDataPrx = sp.columnsDataSfx,
                selfCount,
                lengthColumns = columnsArray.length,
                lengthInColumn = this.cellsInColumn || columnsArray[0].length || 0;

            for (i; i<lengthColumns; i++) {
                selfCount = 0;
                for (selfCount; selfCount<lengthInColumn; selfCount++) {
                    $(columnsArray[i][selfCount]).attr(attrName, columnsDataPrx + i);
                }
            }
            return that;
        },
        /*
         * set max-height cells
         * arguments : array with rows, array with max height data,
         * */
        cellsMaxHeight : function(rows, maxHeightArray, maxStringLength){
            var i = 0,
                length = rows.length,
                cells;

            for (i; i<length; i++) {
                cells = $(rows[i]).children('td', 'th');
                cells.children('div').css('maxHeight', maxHeightArray[i]*maxStringLength);
            }
            return that;
        },
        localStorage : function (obj) {
            var key = that.config.table + '=' + window.location.href,
                data = {
                    tableCells: obj.tableCells
                };
            localStorage.setItem(key, JSON.stringify(obj));
        },
        _cellsWidth : function(co){
            for (el in co) {
                co[el].elements.width(co[el].width);
            }
        }
    }
};

/*
 * createDraggableTemplate : create divs in first column td to draggable
 * arguments : array with table columns and cells elements inner, width for div ( draggable area )
 * default width === 2*border cells width (min width === 10px)
 * */
ColumsResize.prototype.createDraggableTemplate = function( tableColumnsArray, width ) {
    var i = 0,
        width = width && width*2 > 10 ? width*2 : 10,
        length = tableColumnsArray.length - 1,
        template = '<div class="'+this.config.table+this.config.sufixDraggableLine+'" style="position:absolute; ' +
            'width:'+width+'px; height:'+this.config.tableH+'px; top:0; right:0; margin-right:-'+width/2+'px;' +
            'cursor: e-resize;"></div>';

    for (i; i< length; i++) {
        $(tableColumnsArray[i][0])
            .css({position:'relative'})
            .append(template);
    }
};

ColumsResize.prototype.wrapCellData = function(cellsArray) {
    var t = this,
        i = 0,
        length = cellsArray.length,
        temp,
        wrapClass = '_cl-r-cells-wrap';

    for (i; i<length; i++) {
        temp = $(cellsArray[i]).html();
        $(cellsArray[i]).html('<div class="'+wrapClass+'" style="overflow:hidden">'+temp+'</div>');
    }
};

ColumsResize.prototype.createResizeTemplate = function(tableColumnsArray){
    var sp = this.selfProp,
        cfg = this.config,
        table = $(sp.idPfx + cfg.table),
        array = ['left', 'right'],
        length = array.length,
        i = 0,
        lastColumn = tableColumnsArray.length-1,
        tableBorderWidth = parseInt(table.css('border-width')),
        width = tableBorderWidth && tableBorderWidth*2 > 10 ? tableBorderWidth : 10,
        template = '';

    for (i;  i<length; i++) {
        template = '<div class="'+cfg.table+sp.resizeLineSfx+'" style="position:absolute; ' +
        'width:'+width+'px; height:'+cfg.tableH+'px; top:0; '+array[i]+':0; margin-'+array[i]+':-'+width/2+'px;' +
        'cursor: e-resize;"></div>';
        i === 0 ? $(tableColumnsArray[0][0]).css('position', 'relative').append(template) : $(tableColumnsArray[lastColumn][0]).css('position', 'relative').append(template);
    }
};

var cr = new ColumsResize({table: 'table'}).init();