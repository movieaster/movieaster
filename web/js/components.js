angular.module('movieaster.components', []).
  directive('tabs', function() {
    return {
      restrict: 'E',
      transclude: true,
      scope: {},
      controller: function($scope, $element) {
        var panes = $scope.panes = [];
		
        $scope.select = function(pane) {
          angular.forEach(panes, function(pane) {
            pane.selected = false;
          });
          pane.selected = true;
        };
		
        this.addPane = function(pane) {
          if (panes.length === 0) {
            $scope.select(pane);
          }
          panes.push(pane);
        };
      },
      template:
        '<div class="ui-tabs ui-widget ui-widget-content ui-corner-all">' +
          '<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">' +
            '<li class="ui-state-default ui-corner-top" ng-repeat="pane in panes" ng-class="{\'ui-tabs-selected ui-state-active\' : pane.selected}">' +
              '<a href="" ng-click="select(pane)"><img ng-src="{{pane.icon}}"> {{pane.heading}}</a>' +
            '</li>' +
          '</ul>' +
          '<div ng-transclude></div>' +
        '</div>',
      replace: true
    };
  }).
  directive('pane', function() {
    return {
      require: '^tabs',
      restrict: 'E',
      transclude: true,
      scope: { 
	      heading: '@',
	      icon: '@'
      },
      link: function(scope, element, attrs, tabsCtrl) {
        tabsCtrl.addPane(scope);
      },
      template:
        '<div class="ui-tabs-panel ui-widget-content ui-corner-bottom" ng-class="{\'ui-tabs-hide\' : !selected}" ng-transclude>' +
        '</div>',
      replace: true
    };
  }).
  directive('accordion', function() {
    return {
      restrict: 'E',
      transclude: true,
      scope: {},
      controller: function($scope, $element) {
        var panes = $scope.panes = [];
        
        this.addPane = function(pane) {
          if (panes.length === 0) {
            pane.selected = true;
          }
          panes.push(pane);
        };
        
        this.togglePane = function(pane) {
          var toggleState = !pane.selected;
          angular.forEach(panes, function(pane) {
            pane.selected = false;
          });
          pane.selected = toggleState;
        };
      },
      template:
        '<div class="ui-accordion ui-widget ui-helper-reset ui-accordion-icons" ng-transclude>' +
        '</div>',
      replace: true
    };
  }).
  directive('accordionGroup', function() {
    return {
      require: '^accordion',
      restrict: 'E',
      transclude: true,
      scope: {
	      heading: '@',
	      hide: '@'
	  },
      link: function(scope, element, attrs, accordionCtrl) {
	    if(!scope.hide) {
			scope.hide = false;
	    }
        accordionCtrl.addPane(scope);
        scope.toggle = function() {
          accordionCtrl.togglePane(scope);
        };
      },
      template:
        '<div ng-hide="hide">' +
          '<h3 class="ui-accordion-header ui-helper-reset ui-state-default ui-corner-all" ng-class="{\'ui-tabs-selected ui-state-active\' : selected}">' +
            '<span class="ui-icon ui-icon-triangle-1-e" ng-class="{\'ui-icon-triangle-1-s\' : selected}"></span>' +
            '<a href="" ng-click="toggle()">{{heading}}</a>' +
          '</h3>' +      
          '<div style="height: 250px;" class="ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" ng-class="{\'ui-accordion-content-active\' : selected}" ng-transclude>' +
          '</div>' +
        '</div>',
      replace: true
    };
  })
  .directive('autocomplete', function() {
	// based on http://jsfiddle.net/ZguhP/
    return {
        restrict: 'E',
        replace: true,
        scope: {
            minInputLength: '@minInput',
            remoteData: '&',
            placeholder: '@placeholder',
            restrictCombo: '@restrict',
            selectedItem: '=selectedItem',
            selectedCallback: '=selectedCallback'
        },
        template: '<div class="dropdown search" ' + 
        '     ng-class="{open: focused && _choices.length>0}">' + 
        '     <input type="text" ng-model="searchTerm" placeholder="{{placeholder}}" ' + 
        '         tabindex="1" accesskey="s" class="input-medium search-query" focused="focused"> ' + 
        '     <ul class="dropdown-menu"> ' + 
        '         <li ng-repeat="choice in _choices">' + 
        '          <a href="javascript:void(0);" ng-click="selectMe(choice)">{{choice.label}}</a></li>' + 
        '     </ul>' +  
        '</div>',
        controller: function($scope, $element, $attrs) {
            $scope.selectMe = function(choice) {
                $scope.selectedItem = choice;
                $scope.searchTerm = $scope.lastSearchTerm = choice.label;
                $scope.selectedCallback(choice);
                $scope.searchTerm = "";
            };
            $scope.UpdateSearch = function() {
                if ($scope.canRefresh()) {
                    $scope.searching = true;
                    $scope.lastSearchTerm = $scope.searchTerm;
                    try {
                        $scope.remoteData({
                            request: {
                                term: $scope.searchTerm
                            },
                            response: function(data) {
                                $scope._choices = data;
                                $scope.searching = false;
                            }
                        });
                    } catch (ex) {
                        console.log(ex.message);
                        $scope.searching = false;
                    }
                }
            }
            $scope.$watch('searchTerm', $scope.UpdateSearch);
            $scope.canRefresh = function() {
                return ($scope.searchTerm !== "") && ($scope.searchTerm !== $scope.lastSearchTerm) && ($scope.searching != true);
            };
        },
        link: function(scope, iElement, iAttrs, controller) {
            scope._searchTerm = '';
            scope._lastSearchTerm = '';
            scope.searching = false;
            scope._choices = [];
            if (iAttrs.restrict == 'true') {
                var searchInput = angular.element(iElement.children()[0])
                searchInput.bind('blur', function() {
                    if (scope._choices.indexOf(scope.selectedItem) < 0) {
                        scope.selectedItem = null;
                        scope.searchTerm = '';
                    }
                });
            }
        /*      var searchInput1 = angular.element(iElement.children()[0])
                searchInput1.bind("keydown", function(event) {
                    switch (event.keyCode) {
                    case 38:
                        //Up Arrow
                        //console.log('move up');
                        break;
                    case 40:
                        //Down Arrow
                        //console.log('move down');
                        break;
                    case 13:
                        //Enter
                    case 108:
                        //NumKey Enter
                        //console.log('Enter pressed');
                        break;
                    case 32:
                        //Space
                        //console.log('Space pressed');
                        break;
                    case 37:
                        //Escape
                        //console.log('Escape Pressed');
                        break;
                    };

                });
        */
        }
    };
})
.directive("focused", function($timeout) {
    return function(scope, element, attrs) {
        element[0].focus();
        element.bind('focus', function() {
            scope.$apply(attrs.focused + '=true');
        });
        element.bind('blur', function() {
            $timeout(function() {
                scope.$eval(attrs.focused + '=false');
            }, 200);
        });
        scope.$eval(attrs.focused + '=true')
    }
});  
