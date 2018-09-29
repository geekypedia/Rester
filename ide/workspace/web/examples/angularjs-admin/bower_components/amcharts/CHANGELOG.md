# JavaScript Charts and Stock Chart Change Log

## 3.15.2


*    Bug in new Microsoft Edge browser caused category labels and some other texts to disappear. The problem was fixed and the bug reported.
*    Bug fix: Gantt chart used to zoom to wrong dates if minimum/maximum was set.
*    Bug fix: Data labels were not being placed correctly for column charts with reversed value axis.
*    includeAllValues property added to ValueAxis. If you set it to true, minimum and maximum of value axis will not change while zooming/scrolling.


## 3.15.1


*    New feature which allowed to add listeners in JSON config was not working properly in some cases.
*    centerLabels property added to AxisBase. It always force-centers labels of date-based axis (equalSpacing must not be set to true).
*    Radar chart’s fills were drawn incorrectly if connect was set to false and some data was missing.
*    Pie chart could move a pixel back and forward when data was validated and chart redrawn.
*    Using fields for graph of Gantt chart was not always working properly.
*    Wrapping function improved.


## 3.15.0


*    Trend lines now support images. You can have image on both end and start of a trend line. It can be GIF, PNG or SVG (SVG won’t be visible on IE8) images. initialImage and finalImage properties and Image class were added to support this feature.
*    Annotation capabilities of the Export plugin were dramatically enhanced with the ability to add text, shapes, lines and arrows, as well as changing of opacity of items. More info.
    You can add event listeners in JSON chart config now, for example:
    “chartCursor”:{
    “listeners”:[{“event”:”changed”, “method”:handleCursorChange},{“event”:”onHideCursor”, “method”:handleCursorHide}]
    }
*    categoryBalloonText property with default value [[category]] added to ChartCursor. You can have [[toCategory]] tag in there and show category ranges this way.
*    autoWrap now works with vertical CategoryAxis. You should set chart.autoMargins = false or categoryAxis.ignoreAxisWidth = true in order this to work. You might also need to adjust margin to give labels some space.
*    labelRotation, autoRotateCount and autoRotateAngle now work with ValueAxis (when chart is rotated).
*    titleRotation property added to AxisBase. Sets rotation angle of the axis title.
*    autoWrap now works with date-based category axes.
*    onePanelOnly property added to ChartCursorSettings. If you set it to true, Stock Chart will display cursor and value balloons on currently hovered panel only.
*    top and bottom options added to showBalloonAt property of AmGraph. Balloon will be glued to the top/bottom of plot area if you set one of these.
*    pointPosition property added to ValueAxis, default value is “axis”. Alternative value is “middle”. Works with radar charts only. If you set it to “middle”, labels and data points will be placed in the middle between axes. (demo coming)
*    showZeroSlices property added to AmSlicedChart. Default value is false. If you set it to true, the chart will display outlines (if visible) and labels for slices even if their value is 0.
*    “AbsHigh” option added for periodValue property of StockGraph. When data is grouped to longer periods, the graph will show highest absolute value (positive or negative).
*    compareGraph property added to StockGraph. This allows you to set any of AmGraph properties on compared graphs instead of using old-style properties like compareGraphBulletBorderThickness. For example:
    stockGraph.compareGraph = {“bulletBorderThickness”:2, “lineAlpha”:0.4}.
*    If you change graph’s line color using lineColorField, balloon now respects this color and adjusts it’s fill or border color.
*    Title of a chart now auto-wraps if container size is smaller than title itself.
*    Mouse position detection mechanism updated. It is now compatible with CSS3 translate transform (rotation is not yet solved).
*    Radar chart supports date-based data now.
*    Compared graphs of Stock charts ids used to be unpredictable, now they are formed like this: “comparedGraph_” + stockGraph.id + “_” + dataSet.id
*    Bug fix: word-wrapping problems fixed.
*    Bug fix: pie chart with non-default startAngle could not solve label overlapping in some cases.
*    Bug fix: 3D pie chart with startAngle = 270 was placing slices at incorrect z-indices.
*    Bug fix: Stock chart’s grouping to periods with alwaysGroup set to true was not working properly in some cases.
*    Bug fix: the chart could be rendered incorrectly if display style of a container div changed from “none” to “block”
*    Bug fix: legend was missing space between entries and right border.
*    Bug fix: stacked graphs of radar chart were filled to the chart center instead of the graph.
*    Bug fix: radar chart’s axis title was not positioned properly.
*    Bug fix: pie chart’s labels could get under slices in some cases.
*    Bug fix: minimumDate and maximumDate properties of ValueAxis did not accepted dates as strings, even if chart.dataDateFormat was set.
*    Bug fix: 3D pie with big depth3D was not rendered correctly.
*    Bug fix: null values were converted to 0 when parsing data.


## 3.14.5

*    oppositeAxis property added to ChartScrollbar, with default value true. By default, scrollbar is in the opposite side of plot area from the axis. If you set this property to false, scrollbar will be placed next to category/value axis. However it won’t adjust it’s position to accommodate axis labels, so you might need to use offset property to move scrollbar away from the labels.
*    columnWidthField added to AmGanttChart. Allows to specify individual column width for each segment.
*    disableMouseEvents with default value true added to AmBalloon. Helpful in case you have fixed balloon position with some links in the balloon. You should set value of this property to false in order for links in the balloon to be clickable.
*    minorTickLength added to AxisBase. Allows to set length of ticks for minor grid lines (if they are enabled).
*    segmentData added to AmGraph. Works with AmGanttChart only and holds reference to original segment object from data provider.
*    rollOverBand, rollOutBand and clickBand events added to GaugeAxis.
*    url property added to GaugeBand.
*    Bug fix: margins of XY chart were not updated after chart.validateData() call.


## 3.14.3

*    Chart automatically detects path (chart.path variable) to images and other files if amcharts.js or ammap.js file is included as <script> in the document source.
*    Bug fix: click on columns.bullet was not registered if valueLineEnabled was set to true on ChartCursor.
*    Bug fix: chart scrollbar could be messed up if graph.baseValue was set.


## 3.14.2

*    autoResize property added to AmChart and AmStockChart to stop the chart from resizing whenever it’s parent container size changes.
*    path property added to AmChart and AmStockChart. We recommend using this property instead of pathToImages.
*    IMPORTANT: path property, if set will also be pre-pended to non-absolute pattern URLs. This may change the behavior if you use patterns (directly in chart config or theme) with URLs that do not start with protocol or slash)
*    Bug fix: AmCharts.clear() method was not working properly with more than one chart on page.


## 3.14.1

*   Code cleanup and performance tuning.
*   Export plugin updated.
*   adjustPrecision property added to AmPieChart (default is false). Sometimes, because of a rounding, percent of a sum of all slices is not equal to 100%. If you set this to true, when this case happens, number of decimals will be increased so that sum would become 100%.


## 3.13.0

Change log will be available soon. We made a lot of nice features, including plugin which makes chars fully responsive. Meanwhile take a look at this [SVG filters demo](http://www.amcharts.com/demos/using-svg-filters/)!

## 3.12.0

*   The main new feature is that every element of a chart can have class name assigned to it – you must set **addClassNames** property of a chart to **true**. This gives a bunch of new possibilities like controlling the look using CSS, CSS animations and more. Full [list of classNames](http://www.amcharts.com/tutorials/css-class-names/).
*   **classNamePrefix** added to [AmChart](http://docs.amcharts.com/3/javascriptcharts/AmChart) and [AmStockChart](http://docs.amcharts.com/3/javascriptstockchart/AmStockChart), with default value **amcharts**. This prefix is added to all class names which are added to all visual elements of a chart in case **addClassNames** is set to true.
*   **gapField** property added to [AmGraph](http://docs.amcharts.com/3/javascriptstockchart/AmGraph). You can force graph to show gap at a desired data point using this feature, for example, if you set **graph.gapField = “gap”** and then add gap:true in one of your data items in data provider, graph will display a gap at this point.
*   **gapPeriod** property added to [AmGraph](http://docs.amcharts.com/3/javascriptstockchart/AmGraph), with default value 1.1. Using this property you can specify when graph should display gap – if the time difference between data points is bigger than duration of **minPeriod * gapPeriod**, and **connect** property of a graph is set to **false**, graph will display gap.
*   **totalTextOffset** property added to ValueAxis, with default value 0. It specifies distance from data point to total text (used with stacked graphs).
*   **compareGraphLineColor** added to [StockGraph](http://docs.amcharts.com/3/javascriptstockchart/StockGraph).
*   Bug fix: gauge axis labels could display big floating numbers in some cases.
*   Bug fix: **showStockEvents** and **hideStockEvents** used to hide all bullets, not only events.

## 3.11.3

*   Scrolling/zooming on touch devices now works a lot better.
*   Bug fix: fills of step graphs (if color changed in the middle of a graph) were not properly drawn.
*   Bug fix: period values in the legend could add one extra data item in some cases.
*   Some problems with **useUTC** set to true fixed.
*   Bug fix: adding and removing chart with mouse wheel properties enabled could result memory leak.
*   Bug fix: **fixedColumnWith** was not working properly.

## 3.11.2

*   We made quite a lot of changes regarding labels next to data points. Because of that you might require to adjust some properties after the upgrade.  New properties were introduced ([AmGraph](http://docs.amcharts.com/3/javascriptstockchart/AmGraph) class): **labelRotation**, **labelAnchor** and **labelOffset**. These properties will help you to adjust label position in practically any way you need.
*   **fixedColumnWidth** property added to [AmGraph](http://docs.amcharts.com/3/javascriptstockchart/AmGraph). Columns will use value specified for column width and columns won’t adjust size according to the available space.
*   **treatZeroAs** property added to [ValueAxis](http://docs.amcharts.com/3/javascriptcharts/ValueAxis). It can be used with logarithmic scale axis. The fact is that 0 value can not be plotted on logarithmic axis (it’s mathematically impossible). However a lot of people were asking for solution. That’s why we added this property. For example, if you set **<span style="color: #cc6600;">treatZeroAs</span>** to 1,  all the values equal to 0 will be treated as 1 and the chart will render even if you have 0 values in your data.
*   **stepDirection** added to [AmGraph](http://docs.amcharts.com/3/javascriptstockchart/AmGraph), with default value **“right”**. You can set it to **“left”** or **“center”**. It defines to which direction step line graph should draw the step.
*   Bug fix: Funnel chart with very small slices could produce JS error.
*   Bug fix: Funnel chart with labels disabled could produce JS error.
*   Bug fix: if a page had base href set, and url of a page contained # symbol, gradients were not rendered correctly.
*   Bug fix: zooming XY chart with chart cursor could zoom-in to a wrong position.

## 3.11.1

*   **AmCharts.addInitHandler(handler,  [types])** method added to AmCharts.  **handler** is a method which will be called before initializing the chart. **types** is array of strings, specifying which chart types should call this method. If you don’t set any type, all the charts will call this method.  When handler method is called, chart instance is passed as an attribute.  You can use this feature to preprocess chart data or do some other things you need before initializing the chart.
*   Bug fix: cursor zooming of Stock chart with **equalSpacing** set to true could behave incorrectly.
*   Bug fix: columns with rounded corners were displayed incorrectly on IE8 and older (since 3.11.0 only).
*   Bug fix:  JS error occurred if GaugeAxis radius was set in pixels instead of percent.

## 3.11.0

*   **valueLineEnabled** property added to [ChartCursor](http://docs.amcharts.com/3/javascriptcharts/ChartCursor) and [ChartCursorSettings](http://docs.amcharts.com/3/javascriptstockchart/ChartCursorSettings). If you set it to true, horizontal (or vertical if chart is rotated) will be displayed at a mouse position. This works only with Serial charts. Check [demo](http://www.amcharts.com/demos/multiple-panels/).
*   **valueLineBalloonEnabled** added to [ChartCursor](http://docs.amcharts.com/3/javascriptcharts/ChartCursor) and [ChartCursorSettings](http://docs.amcharts.com/3/javascriptstockchart/ChartCursorSettings). If you set it to true, balloon with axis value will be displayed at a mouse position. This works only with Serial
*   charts. Check [demo](http://www.amcharts.com/demos/multiple-panels/).
*   **valueLineBalloonAxis** added to [ChartCursor](http://docs.amcharts.com/3/javascriptcharts/ChartCursor). Is useful if you have more than one value axis and want to specify which axis should display value line balloon.
*   **depth3D** and **angle** properties added to Funnel chart. Allows making funnels 3D. Check [demo](http://www.amcharts.com/demos/3d-funnel-chart/).
*   **topRadius** property added to [AmGraph](http://docs.amcharts.com/3/javascriptstockchart/AmGraph). Works if **depth3D** and **angle** are bigger than 0. If you set topRadius to 1, the chart will display cylinders. In case you’ll set it to 0 – cones. Check [demo](http://www.amcharts.com/demos/3d-cylinder-chart/).
*   **showOnAxis** property added to [AmGraph](http://docs.amcharts.com/3/javascriptstockchart/AmGraph). It can only be used together with **topRadius** (when columns look like cylinders). If you set it to true, the cylinder will be lowered down so that the center of it’s bottom circle would be right on category axis. Check [demo](http://www.amcharts.com/demos/cylinder-gauge/).

## 3.10.4

*   We were so happy with proCandlesticks feature that we didn’t notice that we made it wrong – empty candles should be displayed when current close is bigger than current open. Fixed the problem in this version.

## 3.10.3

*   New property: **proCandlesticks** added to [AmGraph](http://docs.amcharts.com/3/javascriptcharts/AmGraph). If this is set to true, candlesticks will be colored in a different manner – if current close is less than current open, the candlestick will be empty, otherwise – filled with color. If previous close is less than current close, the candlestick will use positive color, otherwise – negative color.
*   New property: **usePrefixes** added to [GaugeAxis](http://docs.amcharts.com/3/javascriptcharts/GaugeAxis).
*   Improvement: If stock chart’s graph has **valueField** set which was not defined in **fieldMappings**, this graph is not displayed in the legend.
*   Bug fix: if **clustered** was set to false, the graph was hidden if only this graph was visible, also the graph did not took full width if more than one clustered graphs where on the same chart.
*   Bug fix: memory leak after **validateNow()** call fixed.
*   Bug fix: **clickSlice** was fired when unhiding slice via legend marker.
*   Bug fix: Stock charts period button was deselected if data set was selected for comparing or a different data set was selected.
*   Bug fix: scrollbar could act strange in some cases (especially if **equalSpacing** was set to **true** or with non date-based data).
*   Bug fix: if **AmCharts.useUTC** was set to **true**, chart was not parsing date strings correctly.
*   Bug fix: Stock chart’s scrollbar did not apply language if not default was used.
*   Bug fix: if multiple charts on the same page used different languages, [ChartCursor](http://docs.amcharts.com/3/javascriptcharts/ChartCursor) balloon used language of the last chart.
*   Bug fix: if custom **urlTarget** was set for a chart, chart kept opening new window instead of opening url in the same one.
*   Bug fix: if **graphBulletSize** was set to 1 on [ChartCursor](http://docs.amcharts.com/3/javascriptcharts/ChartCursor), **graphBulletAlpha** property was ignored.

## 3.10.2

*   Skipped this version

## 3.10.1

*   Bug fix: if a column graph had **newStack** property set to **true** and the value of this graph was missing, the next graphs were stacked in a wrong position.
*   Bug fix: In case multiple value axes chart had line graphs with **connect** set to **false** and there were gaps in the data, gaps might not be displayed.
*   Value axis labels with **logarithmic** set to **true** could use wrong interval in some cases.

## 3.10.0

*   **fillToAxis** property added to [AmGraph](http://docs.amcharts.com/3/javascriptcharts/AmGraph). It can only be used with [AmXYCharts](http://docs.amcharts.com/3/javascriptcharts/AmXYChart). If you set this property to id or reference of your X or Y axis, and the **fillAlphas** is &gt; 0, the area between graph and axis will be filled with color, like in [this demo](http://www.amcharts.com/demos/xy-chart-fills-axis/).
*   **showAt** property added to [StockEvent](http://docs.amcharts.com/3/javascriptstockchart/StockEvent). It will allow you to place bullets at **open**, **close**, **low** or **high** values (mostly used with candlestick/ohlc graphs)
*   **value** property added to [StockEvent](http://docs.amcharts.com/3/javascriptstockchart/StockEvent). It will allow you positioning stock event bullets at any value you want.
*   **useNegativeColorIfDown** property added to [AmGraph](http://docs.amcharts.com/3/javascriptcharts/AmGraph). If **negativeLineColor** and/or **negativeFillColors** are set and **useNegativeColorIfDown** is set to true (default is false), the **line**, **step** and **column** graphs will use these colors for lines, bullets or columns if previous value is bigger than current value. In case you set **openField** for the graph, the graph will compare current value with **openField** value instead of comparing to previous value. Here [is a demo](http://www.amcharts.com/demos/line-different-colors-ups-downs/).
*   **expand** property added to [Guide](http://docs.amcharts.com/3/javascriptcharts/Guide). Works if a guide is added to [CategoryAxis ](http://docs.amcharts.com/3/javascriptcharts/CategoryAxis)and this axis is non-date-based. If you set it to true, the guide will start (or be placed, if it’s not a fill) on the beginning of the **category** cell and will end at the end of **toCategory** cell.
*   **balloonText** property added to [GaugeBand](http://docs.amcharts.com/3/javascriptcharts/GaugeBand). When rolled-over, band will display balloon if you set some text for this property.
*   **labelFunction** property added to [AmSlicedChart ](http://docs.amcharts.com/3/javascriptcharts/AmSlicedChart)(applies for [AmPieChart ](http://docs.amcharts.com/3/javascriptcharts/AmPieChart)and [AmFunnelChart](http://docs.amcharts.com/3/javascriptcharts/AmFunnelChart)). You can use it to format data labels in any way you want.
*   **clearSelection()** method added to [ChartCursor](http://docs.amcharts.com/3/javascriptcharts/ChartCursor). Can be used when **selectWithoutZooming** is set to true and you need to clear the selection made by user.
*   **labelOffset** property added to [AxisBase](http://docs.amcharts.com/3/javascriptcharts/AxisBase). You can use it to adjust position of axes labels. Works both with [CategoryAxis ](http://docs.amcharts.com/3/javascriptcharts/CategoryAxis)and [ValueAxis](http://docs.amcharts.com/3/javascriptcharts/ValueAxis).
*   **switchable** property added to [AmGraph](http://docs.amcharts.com/3/javascriptcharts/AmGraph), with default value set to true. If you set it to false, the graph will not be hidden when user clicks on legend entry.
*   **valueFunction** added to [AmLegend](http://docs.amcharts.com/3/javascriptcharts/AmLegend). You can use it to format value labels in any way you want.
*   **tickPosition** property added to [CategoryAxis](http://docs.amcharts.com/3/javascriptcharts/CategoryAxis). It can be set to **middle** (default) or **start**. Works only with non-date-based data.  [Demo ](http://www.amcharts.com/demos/simple-column-chart/#theme-patterns)of **tickPosition** set to **start**.
*   **labelFunction** added to [AmGraph](http://docs.amcharts.com/3/javascriptcharts/AmGraph). You can use it to format labels of data items in any way you want.
*   Pattern objects can have **color** property now. If your pattern is transparent, the background will be filled with this **color**, like in [this example](http://www.amcharts.com/demos/map-with-patterns/).
*   **graphBulletAlpha** added to [ChartCursor](http://docs.amcharts.com/3/javascriptcharts/ChartCursor). If you make graph’s bullets invisible by setting their opacity to 0 and will set **graphBulletAlpha** to 1, the bullets will only appear at the cursor’s position. Here is a [demo illustrating this](http://www.amcharts.com/demos/step-line-chart/).
*   **labelColorField** added to [CategoryAxis](http://docs.amcharts.com/3/javascriptcharts/CategoryAxis). You can use it to set color of a axis label. Works only with non-date-based data.
*   **maxLabelWidth** added to [AmSlicedChart ](http://docs.amcharts.com/3/javascriptcharts/AmSlicedChart)(applies for [AmPieChart ](http://docs.amcharts.com/3/javascriptcharts/AmPieChart)and [AmFunnelChart](http://docs.amcharts.com/3/javascriptcharts/AmFunnelChart)). If width of the label is bigger than **maxLabelWidth**, it will be wrapped.
*   **labelWidth** property added to [AmLegend](http://docs.amcharts.com/3/javascriptcharts/AmLegend). If width of the label is bigger than **labelWidth**, it will be wrapped.
*   **compareGraphBulletColor** property added to [StockGraph](http://docs.amcharts.com/3/javascriptstockchart/StockGraph).
*   **mouseWheelZoomEnabled** added to [AmSerialChart](http://docs.amcharts.com/3/javascriptcharts/AmSerialChart). Specifies if zooming of a chart with mouse wheel is enabled. If you press shift while rotating mouse wheel, the chart will scroll.
*   **boldLabels** added to [AxisBase](http://docs.amcharts.com/3/javascriptcharts/AxisBase). Labels will be bold if you set it to true.
*   Bug fix: balloons no longer flicker if mouse is moved fast on column charts.
*   Bug fix: **minSelectedTime** and **maxSelectedTime** was not working properly on [AmStockChart](http://docs.amcharts.com/3/javascriptstockchart/AmStockChart).
*   Bug fix: position of data labels in 3D stacked columns was not always accurate.
*   Bug fix: value balloons of [ChartCursor](http://docs.amcharts.com/3/javascriptcharts/ChartCursor) were overlapping if the chart was rotated.
*   Bug fix: if a div containing chart/map had CSS3 transformations applied, the mouse position was calculated incorrectly.
*   Bug fix: **addGuide** method was not working on IE borwsers.
*   Bug fix: Safari could leave previously rendered chart or other objects in the background after the chart was redrawn (only since 3.9.0).

## 3.9.1

*   Bug fix: Stock chart was not working properly with millisecond data.
*   Bug fix: if all graphs of XY chart were hidden from the legend, the chart could start acting weird.
*   Bug fix: sometimes the graph’s balloon became invisible if graph was hidden/unhidden from the legend.
*   Bug fix: angular gauge was not working properly with negative values.
*   Bug fix: if **equalSpacing** was set to **true**, the zooming with chart cursor could zoom-in to a wrong position (Stock chart only).
*   Bug fix: cursors of stock chart could get out of sync in some cases.

## 3.9.0

*   We jumped directly to V 3.9.0 from 3.4.9 in order to keep the same version numbers for charts and maps, as they are often used together. This will help to avoid some misunderstandings.
*   Serious memory leak fixed. It appeared on when chart was redrawn. We noticed this with recent version of Chrome and it seems like this is browser problem. Nevertheless, we found a solution. We strongly recommend to update to this version if you refresh chart with a new data or rebuild it a lot for some other reasons.
*   A possibility to switch languages easily added. Now you can easily change language of a chart (there are not too many texts there, most of them are names of months and weekdays, but still). To do this, you must include lang file from amcharts/lang/ folder and set **chart.language = “de”** or some other language.
*   Exporting chart as SVG now produces one nice file (used to produce separate files for legend and a chart)
*   balloonPointerOrientation added to [ChartCursor ](http://docs.amcharts.com/3/javascriptcharts/ChartCursor)class (also for [ChartCursorSettings](http://docs.amcharts.com/3/javascriptstockchart/ChartCursorSettings)).

## 3.4.10

*   Fix: Saving chart as image was not working properly with IE11 since last release.
*   **recalculateFromDate** property added to [StockPanel](http://docs.amcharts.com/3/javascriptstockchart/StockPanel), allows you to set the date since when the values should be recalculated to percent.
*   Fix: sometimes, when data of StockChart was recalculated to percent, the recalculation started a bit too early which made 0 value to be outside the selection.
*   Fix: new way of using **amExport** was not working properly on [StockChart](http://docs.amcharts.com/3/javascriptstockchart/AmStockChart).

## 3.4.9

*   **clickItem**, **rollOverItem** and **rollOutItem** events added to [AxisBase](http://docs.amcharts.com/3/javascriptcharts/AxisBase). This will allow you to register mouse events on both [CategoryAxis ](http://docs.amcharts.com/3/javascriptcharts/CategoryAxis)and [ValueAxis ](http://docs.amcharts.com/3/javascriptcharts/ValueAxis)labels.
*   Fix: Stock Chart used not to show the beginning or the end of period if the data was grouped but the actual data started/ended somewhere in the middle of this period. This could cause some confusions. Now it is fixed, however if you prefer old behaviour, set **chart.extendToFullPeriod = false;**
*   We added a more easy way to use exporting as an image feature. Charts has **amExport** property now and here is an [AmExport ](http://docs.amcharts.com/3/javascriptcharts/AmExport)class reference.

## 3.4.8

*   **guides** property added to [AmCoordinateChart](http://docs.amcharts.com/3/javascriptcharts/AmCoordinateChart). Instead of adding guides to the axes, you can add them using this property.
*   **showComparedOnTop** property added to [StockPanel](http://docs.amcharts.com/3/javascriptstockchart/AmStockChart). This allows you to set the order of main graph vs compared ones. Default value is **true**.
*   Bug fix: **textAlign **property of [AmBalloon ](http://docs.amcharts.com/3/javascriptcharts/AmBalloon)was not working properly.
*   Bug fix: [GaugeAxis ](http://docs.amcharts.com/3/javascriptstockchart/GaugeAxis)bands might me displayed incorrectly if axis started not on 0 value.
*   Bug fix: if panning was enabled for stock chart, different panels could get out of sync in some cases.
*   Bug fix: if **startAngle** was set for [AmPieChart](http://docs.amcharts.com/3/javascriptcharts/AmPieChart), labels could be displayed at a wrong position.

## 3.4.7

*   You no longer need to add empty data items for dates if you want to show gaps in your data, it’s enough to set **connect** property of [AmGraph](http://docs.amcharts.com/3/javascriptstockchart/AmGraph) to false.
*   Instead of **numberFormatter** and **percentFormatter** properties of [AmChart](http://docs.amcharts.com/3/javascriptcharts/AmChart) we recommend using separate properties – **precision**, **percentPrecision**, **decimalSeparator** and **thousandsSeparator**. [AmGraph](http://docs.amcharts.com/3/javascriptstockchart/AmGraph) class also has **precision** property in case you need a separate precision for a graph. The old formatters will still work.
*   **minBulletSize** property of [AmGraph](http://docs.amcharts.com/3/javascriptstockchart/AmGraph) default changed to 3, as since the 3.4.4 due new way of bullet size calculation, the bubble with the most small value might not be seen at all.
*   Bug fix: Stock chart could freeze when panning it (only if **pan** for cursor was set to true).
*   Bug fix: **alphaField** was ignored by pie chart.
*   Bug fix: [PeriodSelector](http://docs.amcharts.com/3/javascriptstockchart/PeriodSelector) of Stock chart use to select some extra days when predefined period of several years/months was selected.

## 3.4.6

*   **fullWidth** property added to [ChartCursor](http://docs.amcharts.com/3/javascriptcharts/ChartCursor). If set to **true**, instead of a cursor line user will see a fill which width will always be equal to the width of one data item. We’d recommend setting **cusrsorAlpha** to 0.1 or some other small number if using this feature. [Demo of the feature](http://www.amcharts.com/demos/duration-on-value-axis/).
*   **twoLineMode** property added to [CategoryAxis](http://docs.amcharts.com/3/javascriptcharts/CategoryAxis) and [CategoryAxesSettings](http://docs.amcharts.com/3/javascriptstockchart/CategoryAxesSettings). It works only when **parseDates** is set to true and **equalSpacing** is false. If you set it to true, at the position where bigger period changes, category axis will display date strings of bot small and big period, in two rows.
*   **line** marker type is again available for [AmLegend](http://docs.amcharts.com/3/javascriptstockchart/AmLegend)‘s **markerType** property (also markerType of [AmGraph](http://docs.amcharts.com/3/javascriptstockchart/AmGraph) if you need to set custom types for your graphs).
*   [GaugeAxis](http://docs.amcharts.com/3/javascriptcharts/GaugeAxis) properties **valueInterval** and **minorTickInterval** doesn’t have default values since this version, as it might cause problems with big numbers. Instead we added **gridCount** property which is 5 by default. Note, [GaugeAxis](http://docs.amcharts.com/3/javascriptcharts/GaugeAxis) doesn’t adjust **gridCount**, so you should check your values and choose a proper **gridCount** which would result grids at round numbers.

## 3.4.5

*   **newStack** property added to **AmGraph**. If you set it to true, column chart will begin new stack. This allows having [Clustered and Stacked column/bar](http://www.amcharts.com/demos/stacked-clustered-column-chart/) chart.
*   Bug fix: since  3.4.4 old IE browsers failed to display chart if legend position was **left** or **right**<span style="line-height: 1.428571429;"> </span>

## 3.4.4

*   You can set **divId** for [StockLegend ](http://docs.amcharts.com/3/javascriptstockchart/StockLegend)now too. This should be id or reference to a div outside the chart where you want a legend  to appear.
*   Adjusted algorithm of bullet size calculation for Bubble (XY) chart.
*   **showBulletsAt** property added to [AmGraph](http://docs.amcharts.com/3/javascriptcharts/AmGraph). Works with **candlestick** graph type, you can set it to **open**, **close**, **high**, **low**. If you set it to **high**, the events will be shown at the tip of the high line.
*   New property, **minDistance** added to [AmGraph](http://docs.amcharts.com/3/javascriptcharts/AmGraph). Default value is 1. It is useful if you have really lots of data points. Based on this property the graph will omit some of the lines (if the distance between points is less that **minDistance**, in pixels). This will  not affect the bullets or indicator in anyway, so the user will not see any difference (unless you set minValue to a bigger value, let say 5), but will increase performance as less lines will be drawn. By setting value to a bigger number you can also make your lines look less jagged.
*   Changed default value of panEventsEnabled (property of [AmChart ](http://docs.amcharts.com/3/javascriptcharts/AmChart)and [PanelsSettings ](http://docs.amcharts.com/3/javascriptstockchart/PanelsSettings)classes) **from** false to **true**.
*   Bug fix: right scrollbar grip of Stock chart was not working properly with **equalSpacing** set to true and **minPeriod** was not **DD** (since 3.4.3 version only).
*   Bug fix: if amcharts.js and ammap.js was included for several times (you shouldn’t do that, but still), the charts were not working properly.
*   Bug fix: if the slice of a pie/funnel was hidden and the method rollOverSlice(slice) was called from outside, the balloon was still shown.
*   Bug fix: Sometimes part of a legend was cut-off when labels were long enough and legend position was **left** or **right.**
*   Bug fix: outline of funnel chart slices had some extra unnecessary lines.

## 3.4.3

*   **processDelay** property added to AmCharts class. If you set **AmCharts.processDelay = 200;** all the charts on the page will be rendered with 200 ms intervals. This is very comfortable if you have a lot of charts on the page and do not want to overload the device CPU. </span>
*   A third parameter, **delay** was added to **AmCharts.makeChart** method. It specifies the delay in ms, at which the chart must be rendered, for example: **AmCharts.makeChart(“chartDiv”, {chart config}, 200);**</span>
*   **offset** property added to ChartScrollbar. Allows to place scrollbar apart from plot area.
*   **autoRotateCount** and **autoRotateAngle** properties added to CategoryAxis. Works only when dates are not parsed. Axis labelsl will be rotated if the number of series will be equal or exceed **autoRotateCount** value.
*   **rollOverGraph** and **rollOutGraph** events added to AmCoordinateChart.
*   **changed** event of stock chart’s period selector passes the original mouse event as event property.
*   Bug fix: Stock chart used to select one extra period when dates were entered in input fields and **equalSpacing** was set to true;
*   Bug fix: some issues with floating point errors fixed
*   Bug fix: zoom-out button border was always visible on IE8.
*   Bug fix: funnel chart was not working properly with labels disabled.
*   filesaver.js was updated so that in case it is included with IE8 and older browsers, it wouldn’t throw JS error.

## 3.4.2

*   Bug fix: if pie slice had no label, the external method **rollOverSlice(index)** was not working
*   Bug fix: x switch of the legend position adjusted
*   Bug fix: when **autoWrap** for category axis was set to **true**, in some cases axis title was cut.
*   **markPeriodChange** was set to true in [CategoryAxesSettings](http://docs.amcharts.com/3/javascriptstockchart/CategoryAxesSettings).

## 3.4.1

*   Patterns theme added.
*   Themes were updated a bit.
*   Labels of angular gauge axis adjusted.
*   When scrolling serial/stock charts with mousewheel (chart.mouseWheelScrollEnabled must be set to true), if user press shift button, the chart will zoom-in or zoom-out;
*   **adjustment** property added to  [ChartCursor](http://docs.amcharts.com/3/javascriptcharts/ChartCursor). Default value is 0, if you set it to -1, the balloon will show near previous, if you set it to 1 – near next data point.

## 3.4.0

*   Link to amCharts.com site in a free version was made less noticeable – it uses chart’s font color and font size and you can adjust it’s position using **creditsPosition** property of [AmChart](http://docs.amcharts.com/3/javascriptcharts/AmChart). Possible values are: **top-left**, **top-right**, **bottom-right** and **bottom-left**. This will help you to achieve better layout of a chart.
*   We fixed typo of [ChartCursor](http://docs.amcharts.com/3/javascriptcharts/ChartCursor) property – it was showNextAvalable and now is **showNextAvailable**. The old one won’t work.
*   Since now you can scroll serial and stock charts with mouse wheel. To enable this, set **chart.mouseWheelScrollEnabled = true** (default is false).
*   **moved** event added to [ChartCursor](http://docs.amcharts.com/3/javascriptcharts/ChartCursor). It is dispatched every time the mouse is moved. The event object has the following properties: **x**, **y** (coordinates of the cursor), **chart** and **zooming**.
*   **axisX** and **axisY** properties added to [AxisBase](http://docs.amcharts.com/3/javascriptcharts/AxisBase). They are read-only and returns **x** and **y** positions of the axis.
*   **unit** and **unitPosition** (with possible values **left** and **right**) added to [GaugeAxis](http://docs.amcharts.com/3/javascriptcharts/GaugeAxis) class.
*   **autoWrap** property added to [CategoryAxis](http://docs.amcharts.com/3/javascriptcharts/CategoryAxis), with default value **false**. If you set it to **true**, the axis labels will be wrapped if they won’t fit in the allocated space.
*   **minHorizontalGap** (default 75) and **minVerticalGap** (35) properties added to [AxisBase](http://docs.amcharts.com/3/javascriptcharts/AxisBase). They are used to calculate the number of grid lines when **autoGridCount** is **true**. You can modify these values to have more or less grid lines.

## 3.3.6

*   Bug fix – charts with legend could fail if there was a Google Analytics script in the page.
*   **stepDirection** property added to [AmGraph](http://docs.amcharts.com/3/javascriptcharts/AmGraph). If you set it to **left**, step line graph will draw the step to the left of the date/category.

## 3.3.5

*   Bug fix: 3D pie chart was not rendered in IE8 and older (since 3.3.4 version only).
*   Candlestick graphs can display patterns.
*   Old listeners are removed automatically if the same listener was added, this helps to avoid duplicate calls of event handlers.
*   Bug fix: \n in **labelText** of [AmGraph](http://docs.amcharts.com/3/javascriptcharts/AmGraph) is now properly displayed as new line.

## 3.3.4

*   Export as image script fixed – bullets of charts with scrollbars were not exported.
*   dataContext property added to [SerialDataItem](http://docs.amcharts.com/3/javascriptcharts/SerialDataItem). It holds reference to original data object and might be used when using **labelFunction** to format custom balloon text and in some other cases.
*   XY chart can display bullets with patterns (if **valueField** is set).

## 3.3.3

*   **hideBalloonTime** property added to [AmChart](http://docs.amcharts.com/3/javascriptcharts/AmChart) class, default value is 150 (milliseconds). It sets time after which balloon is hidden if user rolls-out of the object. Increasing the time might help to prevent balloon flickering while moving the mouse over the object.
*   **useLineColorForBulletBorder** property added to [AmGraph](http://docs.amcharts.com/3/javascriptcharts/AmGraph). Might help in some situations, especially when using themes.
*   3D charts now look a lot better with patterns.
*   **endWidth** property added to [GaugeArrow ](http://docs.amcharts.com/3/javascriptcharts/GaugeArrow)(default value 0). This will allow having more modern, rectangular arrows.
*   [**facePattern**](http://docs.amcharts.com/3/javascriptcharts/AmAngularGauge) property added to [AmAngularGauge](http://docs.amcharts.com/3/javascriptcharts/AmAngularGauge). You can fill gauge’s face with some pattern using it.
*   Bug fix: new lines were ignored in balloons.

## 3.3.2

*   You can now set theme for all the charts on your page by setting: **AmCharts.theme = AmCharts.themes.light;** If you are creating charts using JavaScript API, not JSON, then this is quite a comfortable way, as you won’t need to pass theme to each object you create.
*   Bug fix: **rendered** event was fired only on first render, now it is fired each time the chart is rendered after **chart.validateNow();** method is called. This bug caused the export buttons to disappear after the **validateNow()** method.
*   **showNextAvalable** property added to [ChartCursor](http://docs.amcharts.com/3/javascriptcharts/ChartCursor) (default is **false**). If **true**, the graph will display balloon on next available data point if currently hovered item doesn’t have value for this graph.
*   **periodSpan** property added to [AmGraph](http://docs.amcharts.com/3/javascriptcharts/AmGraph) (default is 1). This property can be used by step graphs – you can set how many periods one horizontal line should span.
*   **end** option added to **pointPosition** property of [AmGraph](http://docs.amcharts.com/3/javascriptcharts/AmGraph).

## 3.3.1

*   Bug fix:  **\n** was replaced with **&lt;br&gt;** in category axis and the tag was displayed.
*   Bug fix: if **lineColorField** or **dashLengthField** or **fillColorsField** was set, the graph could loose the setting if zoomed.

## 3.3.0

*   Since this version our charts and maps support themes. This means that instead of setting every property for each graph or axis or any other object, you can set new defaults in a theme file. This will make developers life a lot easier. Currently you can find three themes in **amcharts/themes** folder – **dark.js**, **light.js** and **chalk.js** To set a theme for a chart, simply set theme property to the name of the file: **theme:”light”**. Note, this will work only if you are creating chart using JSON config. If you do it in old way (JSON config is supported since v 3.2.0), you should pass theme object for each new object you build, for example: **var graph = new AmCharts.AmGraph(AmCharts.themes.light);** We will be adding more themes soon. Check **\_usingThemes.html** file in samples folder to see themes in action.
*   **patterns** property added to [AmSlicedChart](http://docs.amcharts.com/3/javascriptcharts/AmSlicedChart) and [AmCoordinateChart](http://docs.amcharts.com/3/javascriptcharts/AmCoordinateChart) – instead of setting a pattern for a slice/graph, you can pass array of patterns using this property.
*   You can now control zoom-out buttons with the following new properties of [AmRectangularChart](http://docs.amcharts.com/3/javascriptcharts/AmRectangularChart): **zoomOutButtonImageSize**, **zoomOutButtonImage**, **zoomOutButtonColor**, **zoomOutButtonAlpha**, **zoomOutButtonRollOverAlpha**, **zoomOutButtonPadding**.

## 3.2.0

*   **AmCharts.makeChart(divID, chartConfig);** method added. **divID** is id of a **div** where your chart should appear. **chartConfig** is JSON object with chart configuration. Check examples with **_JSON_** prefix in samples folder to see this in action.
*   **type** property added to [AmChart](http://docs.amcharts.com/3/javascriptcharts/AmChart) class. It is required to specify type to one of the following, when creating charts from JSON config: **serial**, **xy**, **radar**, **pie**, **gauge**, **funnel**, **map**, **stock.**
*   A possibility to export charts as image/pdf/svg added for all modern browsers except IE9 (IE10 is supported). The exporting doesn’t require any server side software and is made using JavaScript libraries only. Check samples with **_exporting_** prefix to see this in action. Exporting to SVG doesn’t work very properly with stock chart or charts with legend (will offer saving multiple files).
*   You can set any legend items via **data** property of [AmLegend](http://docs.amcharts.com/3/javascriptcharts/AmLegend), for example: **legend.data = [{title:”first”, color:”#CC0000″, value:50}, {title:”second”, color:”#00CC00″, value:100}];** This allows creating any legend items you want. Call **chart.legend.validateNow();** if you change legend’s data at run time.
*   [AmAngularGauge](http://docs.amcharts.com/3/javascriptcharts/AmAngularGauge) supports legend now.
*   **gridAboveGraphs**<span style="line-height: 1.428571429;"> property added to </span>[AmCoordinateChart](http://docs.amcharts.com/3/javascriptcharts/AmCoordinateChart)<span style="line-height: 1.428571429;">. This allow to show grid lines above your graphs, as world-famous </span><span style="line-height: 1.428571429;">data visualization guru Edward Tufte suggests. Note, this won’t work properly with 3D charts.</span>
*   You can also use negative values from -90 to -1 for **labelRotation** property of [CategoryAxis](http://docs.amcharts.com/3/javascriptcharts/CategoryAxis) since now.
*   Bug fix: if a chart with scrollbar was rotated after the chart is created, the scrollbar’s graph was shifted to a wrong position.
*   Bug fix: column graph type wasn’t displayed in chart scrollbar (since 3.1.0).
*   Bug fix: step line with changing line color was rendered incorrectly if some values were missing.
*   Bug fix: **labelPosition** values **inside** and **middle** were not working properly with bar charts.
*   Bug fix: [AmAngularGauge](http://docs.amcharts.com/3/javascriptcharts/AmAngularGauge) chart wasn’t firing **rendered** event.

## 3.1.1

*   Bug fix: FireFox error messages about style declarations fixed.
*   Bug fix: **maxWidth** property of [AmBalloon](http://docs.amcharts.com/3/javascriptcharts/AmBalloon) was ignored.

## 3.1.0

*   Great new features added – charts now support patterns (can fill bars, lines and slices with images) and can simulate hand drawn charts – the lines will be a bit distorted and produce a nice effect. Check our new [inspiring samples](http://www.amcharts.com/inspiration/)  to see new possibilities in action.
*   Patterns can be set for entire graphs or for individual columns/slices. In case you want to set pattern for a graph, use **pattern** property of [AmGraph](http://docs.amcharts.com/3/javascriptcharts/AmGraph). If you want to set individual pattern for a column or slice, describe patterns in chart’s data provider and set **patternField** for a graph or pie/funnel chart. Value of pattern should be object with **url**, **width**, **height** of an image, optionally it might have **x**, **y**, **randomX** and **randomY** values. For example: **graph.pattern = {“url”:”../amcharts/patterns/black/pattern1.png”, “width”:4, “height”:4};** check amcharts/patterns folder for some patterns. You can create your own patterns and use them. Note, **x**, **y**, **randomX** and **randomY** properties won’t work with IE8 and older.
*   <span style="line-height: 1.428571429;">if you set **chart.handDrawn = true**, the lines of a chart will be distorted and will produce hand-drawn effect. </span>You can also modify **handDrawScatter** (default value is 2) and **handDrawThickness** (default value 1)  of [AmChart](http://docs.amcharts.com/3/javascriptcharts/AmChart) values for more scattered view.
*   **offsetY** and **offsetX** properties added to [AmBalloon](http://docs.amcharts.com/3/javascriptcharts/AmBalloon). Specifies the distance from the mouse position to balloon’s pointer. You might want to increase distance when using hand drawn style.

## 3.0.0

*   <span style="line-height: 1.428571429;">As not all users require all type of charts, we spilt the js file into several files – one main **amcharts.js** file, plus </span>a separate js file for each chart type. This means you can include only the charts you need. If you are worried about number of requests, you can simply copy/paste the source of the charts you use to the main file.
*   <span style="line-height: 1.428571429;">Although we changed some default values in order to improve usability of the charts, the only thing you should worry </span>about when upgrading from v2 to v3 is the feature mention above – you should include two or more js files in order your charts to work. If you don’t like the changed defaults, you can always set them to the previous values in your chart
*   <span style="line-height: 1.428571429;">New chart type added – Funnel / Pyramid chart. </span>As this chart type has a lot of in common with pie chart, we created one base class for these chart types – [AmSlicedChart](http://docs.amcharts.com/3/javascriptcharts/AmSlicedChart). [AmPieChart](http://docs.amcharts.com/3/javascriptcharts/AmPieChart) and [AmFunnelChart](http://docs.amcharts.com/3/javascriptcharts/AmFunnelChart) now extend this class.
*   New chart type added – [AngularGauge](http://docs.amcharts.com/3/javascriptcharts/AmAngularGauge). Supports multiple axes and multiple arrows.
*   We added lots of new features to our charts and with these features you can create new chart types, like:
*   Horizontal or vertical bullet chart – bulletChart.html
*   Waterfall chart – waterFallChart.html
*   Step chart without risers – lineStepNoRisers.html
*   Error chart (both Serial and XY) – errorChart.html
*   Possibility to show minor grid for both Category and Value axis. **minorGridEnabled** (default value false) and **minorGridAlpha** (default 0.07) properties added to [AxisBase](http://docs.amcharts.com/3/javascriptcharts/AxisBase) class.
*   Possibility to change line graphs’ line/fill color on any data point to create highlighted sections of the graph. To achieve this, you should set **lineColorField** and/or **fillColorsField** for your graph and have a field in your data which would contain color values at a point where you want the graph to change it’s color.
*   Possibility to switch line from solid to dashed. Columns can also have dashed outline. To achieve this, you should set **dashLengthField** for your graph and have a field in your data which would contain dash length value at a point where you want the graph to change from solid to dashed or vice versa.
*   Date strings in data now supported. Even if your chart parses dates, you can pass them as strings in your data – all you need to do is to set data date format and the chart will parse dates to date objects. This means that now data for date-based chart can be in legit JSON format. **dataDateFormat** property added to [AmSerialChart](http://docs.amcharts.com/3/javascriptcharts/AmSerialChart) and [AmStockChart](http://docs.amcharts.com/3/javascriptstockchart/AmStockChart).
*   When moving chart cursor over the chart, hovered bullets can change their size. If a graph has bullets and you added [ChartCursor](http://docs.amcharts.com/3/javascriptcharts/ChartCursor) to the chart, bullets will become bigger when char cursor is over them. **graphBulletSize** property with default value 1.7 added to [ChartCursor](http://docs.amcharts.com/3/javascriptcharts/ChartCursor). If you want to disable this feature, set the value to 1.
*   Legend can now display period value. When user is not hovering the chart, legend can show **sum**, **average**, **open**, **close**, **low** or **high** values of selected period. **periodValueText** added to [AmLegend](http://docs.amcharts.com/3/javascriptcharts/AmLegend) and **legendPeriodValueText** added to [AmGraph](http://docs.amcharts.com/3/javascriptcharts/AmGraph) to achieve this. The tags should be made out of two parts – the name of a field (value / open / close / high / low) and the value of the period you want to be show – open / close / high / low / sum / average / count. For example: **[[value.sum]]** means that sum of all data points of value field in the selected period will be displayed.
*   To achieve the same with stock chart, we added **periodValueTextRegular** and **periodValueTextComparing** proprties to [StockLegend](http://docs.amcharts.com/3/javascriptstockchart/StockLegend). To show percent period values, you should add **percent.** prefix for your tag, for example: **[[percents.value.close]]** means that last percent value of a period will be displayed.
*   Legend markers can now mirror graph’s settings, displaying a line and a real bullet as in the graph itself. **useGraphSettings** property with default value false was added to [AmLegend](http://docs.amcharts.com/3/javascriptstockchart/AmLegend). Note, we also removed **line** and **dashedLine** marker types because of this – use the **useGraphSettings** feature in case you need lines as markers in the legend.
*   Legend now supports custom markers (images). **customMarker** property was added to [AmGraph](http://docs.amcharts.com/3/javascriptstockchart/AmGraph). You should set path to the image which should be displayed in the legend.
*   Diamond bullet type added to [AmGraph](http://docs.amcharts.com/3/javascriptstockchart/AmGraph). Set **graph.bullet = “diamond”** to use it.
*   Dynamic bullet size based on value axis / Error chart. Error chart is a regular serial or XY chart with bullet type set to **errorX** or **errorY**. The graph should know which axis should be used to determine the size of this bullet – that’s when **graph.bulletAxis** property should be set. Besides that, you should also set **graph.errorField**. You can also use other bullet types with this feature too. For example, if you set **bulletAxis** for XY chart, the size of a bullet will change as you zoom the chart.
*   You can specify custom column width for each graph individually. **columnWidth** property added to [AmGraph](http://docs.amcharts.com/3/javascriptstockchart/AmGraph). Note, you set relative width here (0 – 1), not width in pixels.
*   Columns can be overlaid on other columns, without making axis as stacked. **clustered** property added to [AmGraph](http://docs.amcharts.com/3/javascriptstockchart/AmGraph). In case you want to place graph’s columns in front of other columns, set it to false.
*   Resize grips were made bigger to make life easier for users on touch devices.
*   Balloons can now display any HTML and CSS inside them. This means you can add images, format text or display just about any HTML/CSS content. Because of this new feature, we removed **textShadow** property of [AmBalloon](http://docs.amcharts.com/3/javascriptstockchart/AmBalloon) in this version.
*   Balloon now can animate from point to point and also fade out when user moves away from the chart. **animationDuration** and **fadeOutDuration** properties added to [AmBalloon](http://docs.amcharts.com/3/javascriptstockchart/AmBalloon), with default values 0.3. **animationDuration** property was also added to [ChartCursor](http://docs.amcharts.com/3/javascriptstockchart/ChartCursor), so that the cursor line would also animate to its position.
*   Balloon now can display shadow. **shadowColor** (default #000000) and **shadowAlpha** (default 0.4) added to [AmBalloon](http://docs.amcharts.com/3/javascriptstockchart/AmBalloon).
*   Some default values of [AmBalloon](http://docs.amcharts.com/3/javascriptstockchart/AmBalloon) changed for a better usability – **adjustBorderColor** to true, **cornerRadius** to 0, **pointerWidth** to 6, **color** to #000000.
*   Stock chart can display scrollbar on top of the chart – you should set position property of [ChartScrollbarSettings](http://docs.amcharts.com/3/javascriptstockchart/ChartScrollbarSettings) to **“top”**.<span style="line-height: 1.428571429;"> </span>
