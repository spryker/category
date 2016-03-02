/**
 *
 * Spryker alert message manager
 * @copyright: Spryker Systems GmbH
 *
 */

'use strict';

// var SprykerAjax = require('vendor/spryker/spryker/Bundles/Gui/assets/Zed/modules/legacy/SprykerAjax');

var showLoaderBar = function(){
    $('#category-loader').removeClass('hidden');
};

var closeLoaderBar = function(){
    $('#category-loader').addClass('hidden');
};

SprykerAjax.getCategoryTreeByIdCategoryNode = function(idCategoryNode) {
    var options = {
        'id-category-node': idCategoryNode
    };
    this
        .setUrl('/category/index/node')
        .setDataType('html')
        .ajaxSubmit(options, 'displayCategoryNodesTree');
};

SprykerAjax.updateCategoryNodesOrder = function(serializedCategoryNodes){
    showLoaderBar();
    this.setUrl('/category/node/reorder').ajaxSubmit({
        'nodes': serializedCategoryNodes
    }, 'updateCategoryNodesOrder');
};

/*
 * @param ajaxResponse
 * @returns string
 */
SprykerAjaxCallbacks.displayCategoryNodesTree = function(ajaxResponse){
    $('#category-node-tree').removeClass('hidden');
    $('#categories-list').html(ajaxResponse);
    closeLoaderBar();
};

SprykerAjaxCallbacks.updateCategoryNodesOrder = function(ajaxResponse){
    closeLoaderBar();
    if (ajaxResponse.code === this.codeSuccess) {
        swal({
            title: "Success",
            text: ajaxResponse.message,
            type: "success"
        });
        return true;
    }

    swal({
        title: "Error",
        text: ajaxResponse.message,
        type: "error"
    });
};

module.exports = {
    showLoaderBar: showLoaderBar,
    closeLoaderBar: closeLoaderBar
};
