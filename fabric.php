<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
    <script src="assets/fabric.min.js"></script>
    <title>fabric</title>
    <style>
        #canvas {
            background-color: burlywood;
            width: 100%
        }
    </style>
</head>

<body>
    <div id="container" class="container">


        <div class="row">
            <div class="col-md-6">
                <div class="btn btn-info btn-block">Chức năng</div>
                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link js-select-mode active" data-mode="" data-toggle="tab" href="#info">No mode</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link js-select-mode" data-mode="drawPolygon" data-toggle="tab" href="#drawPolygon">Vẽ đa giác</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link js-select-mode" data-mode="modeDraw" data-toggle="tab" href="#modeDraw">Vẽ ô vuông</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link js-select-mode" data-mode="fillColor" data-toggle="tab" href="#fillColor">Tô màu</a>
                    </li>
                </ul>

                <!-- Tab panes -->
                <div class="tab-content">
                    <div class="tab-pane container active" id="info">
                        <p>Thông tin</p>
                        <div class="row">
                            <button class="btn btn-warning js-export-data">Export json</button>
                            <button class="btn btn-warning js-import-data">Import json</button>
                            <div class="col-12 mt-2">
                                <div class="json-export-contain"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane container fade" id="drawPolygon">
                        <div class="row">
                            <p class="bg-warning mt-1 w-100">dblick để bắt đầu và kết thúc vẽ</p>
                        </div>
                        <div class="row">
                            <div class="btn btn-success js-group">Group</div>
                            <div class="btn btn-info js-ungroup">UnGroup</div>
                        </div>
                    </div>
                    <div class="tab-pane container fade" id="modeDraw">Vẽ ô vuông</div>
                    <div class="tab-pane container fade" id="fillColor">
                        <div class="row mt-1">
                            <select class="form-control" id="sel_color">
                                <option value="" readonly>Chọn màu</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-md-6">
                <canvas name="canvas" id="canvas" width="540" height="500"></canvas>
            </div>

        </div>
    </div>

    <script>
        (function() {
            const canvas = document.querySelector('#canvas');
            let listMode = {}
            const fabricCanvas = new fabric.Canvas(canvas);
            fabricCanvas.perPixelTargetFind = true;
            fabricCanvas.targetFindTolerance = 5;
            // fabricCanvas.hasControls = fabricCanvas.hasBorders = false;

            function Point(x, y) {
                this.x = x;
                this.y = y;
            };

            const drawPolygon = {
                roof: null,
                roofPoints: [],
                lines: [],
                lineCounter: 0,
                drawingObject: {
                    type: '',
                    background: '',
                    border: '',
                },

                setStartingPoint: function(options) {
                    this.x = options.e.pageX - fabricCanvas._offset.left;
                    this.y = options.e.pageY - fabricCanvas._offset.top;
                },
                makeRoof: function() {
                    var left = this.findLeftPaddingForRoof(this.roofPoints);
                    var top = this.findTopPaddingForRoof(this.roofPoints);

                    this.roofPoints.push(new Point(this.roofPoints[0].x, this.roofPoints[0].y))
                    var roof = new fabric.Polyline(this.roofPoints, {
                        fill: 'rgba(0,0,0,0.01)',
                        stroke: '#58c'
                    });
                    roof.set({

                        left: left,
                        top: top,

                    });


                    return roof;
                },

                findTopPaddingForRoof: function() {
                    var result = 999999;
                    for (var f = 0; f < this.lineCounter; f++) {
                        if (this.roofPoints[f].y < result) {
                            result = this.roofPoints[f].y;
                        }
                    }
                    return Math.abs(result);
                },

                findLeftPaddingForRoof: function() {
                    var result = 999999;
                    for (var i = 0; i < this.lineCounter; i++) {
                        if (this.roofPoints[i].x < result) {
                            result = this.roofPoints[i].x;
                        }
                    }
                    return Math.abs(result);
                },

                onGroup: function() {
                    if (!fabricCanvas.getActiveObject()) {
                        return;
                    }
                    if (fabricCanvas.getActiveObject().type !== 'activeSelection') {
                        return;
                    }
                    fabricCanvas.getActiveObject().toGroup().perPixelTargetFind = true;
                    fabricCanvas.requestRenderAll();
                },

                onUnGroup: function() {
                    if (!fabricCanvas.getActiveObject()) {
                        return;
                    }
                    if (fabricCanvas.getActiveObject().type !== 'group') {
                        return;
                    }
                    fabricCanvas.getActiveObject().toActiveSelection();
                    fabricCanvas.requestRenderAll();
                },

                onDbclick: function(options) {
                    if (this.drawingObject.type == 'roof') {
                        this.drawingObject.type = '';
                        this.lines.forEach(function(value, index, ar) {
                            fabricCanvas.remove(value);
                        });
                        this.roof = this.makeRoof(this.roofPoints);
                        fabricCanvas.add(this.roof);
                        fabricCanvas.renderAll();

                        //clear arrays
                        this.roofPoints = [];
                        this.lines = [];
                        this.lineCounter = 0;
                    } else {
                        this.drawingObject.type = "roof";
                        drawPolygon.onMouseDown(options)
                    }

                },
                onMouseDown: function(options) {
                    if (this.drawingObject.type == "roof") {
                        fabricCanvas.selection = false;
                        this.setStartingPoint(options); // set x,y
                        this.roofPoints.push(new Point(this.x, this.y));
                        var points = [this.x, this.y, this.x, this.y];
                        this.lines.push(new fabric.Line(points, {
                            strokeWidth: 3,
                            selectable: false,
                            stroke: 'red'
                        }));
                        fabricCanvas.add(this.lines[this.lineCounter]);
                        this.lineCounter++;
                    }
                },
                onMouseUp: function(options) {
                    fabricCanvas.selection = true;
                },
                onMouseMove: function(options) {
                    if (this.lines[0] !== null && this.lines[0] !== undefined && this.drawingObject.type == "roof") {
                        this.setStartingPoint(options);
                        this.lines[this.lineCounter - 1].set({
                            x2: this.x,
                            y2: this.y
                        });
                        fabricCanvas.renderAll();
                    }
                },

                install: function() {
                    if (this.drawingObject.type == 'roof') {
                        this.drawingObject.type = "";
                        this.lines.forEach(function(value, index, ar) {
                            fabricCanvas.remove(value);
                        });

                        this.roof = this.makeRoof(this.roofPoints);
                        fabricCanvas.add(this.roof);
                        fabricCanvas.renderAll();
                    } else {
                        this.drawingObject.type = "roof";
                    }

                    this.mouseDown = function(e) {
                        drawPolygon.onMouseDown(e)
                    };

                    this.mouseMove = function(e) {
                        drawPolygon.onMouseMove(e)
                    };

                    this.mouseUp = function(e) {
                        drawPolygon.onMouseUp(e)
                    };

                    this.dbclick = function(e) {
                        drawPolygon.onDbclick(e)
                    };

                    fabricCanvas.on('mouse:down', this.mouseDown);
                    fabricCanvas.on('mouse:move', this.mouseMove);
                    fabricCanvas.on('mouse:up', this.mouseUp);
                    fabricCanvas.on('mouse:dblclick', this.dbclick);

                    $('.js-group').on('click', drawPolygon.onGroup);
                    $('.js-ungroup').on('click', drawPolygon.onUnGroup);
                },
                uninstall: function() {
                    this.drawingObject.type = "";
                    fabricCanvas.off('mouse:down', this.mouseDown);
                    fabricCanvas.off('mouse:move', this.mouseMove);
                    fabricCanvas.off('mouse:up', this.mouseUp);
                    fabricCanvas.off('mouse:dblclick', this.dbclick);
                    $('.js-group').off('click', drawPolygon.onGroup);
                    $('.js-ungroup').off('click', drawPolygon.onUnGroup);
                }

            }

            const modeDraw = {
                dragging: false,
                freeDrawing: false,
                initialPos: false,
                rect: '',
                bounds: true,
                options: {
                    drawRect: true,
                    rectProps: {
                        stroke: '#333',
                        strokeWidth: 1,
                        strokeDashArray: [10, 5],
                        fill: '',
                    }
                },
                onMouseDown: function(e) {
                    this.dragging = true;
                    if (!this.freeDrawing) {
                        return
                    }
                    this.initialPos = {
                        ...e.pointer
                    }
                    this.bounds = {}
                    if (this.options.drawRect) {
                        this.rect = new fabric.Rect({
                            left: this.initialPos.x,
                            top: this.initialPos.y,
                            width: 0,
                            height: 0,
                            ...this.options.rectProps
                        });
                        fabricCanvas.add(this.rect)
                    }
                },
                update: function(pointer) {
                    if (this.initialPos.x > pointer.x) {
                        this.bounds.x = Math.max(0, pointer.x)
                        this.bounds.width = this.initialPos.x - this.bounds.x
                    } else {
                        this.bounds.x = this.initialPos.x
                        this.bounds.width = pointer.x - this.initialPos.x
                    }
                    if (this.initialPos.y > pointer.y) {
                        this.bounds.y = Math.max(0, pointer.y)
                        this.bounds.height = this.initialPos.y - this.bounds.y
                    } else {
                        this.bounds.height = pointer.y - this.initialPos.y
                        this.bounds.y = this.initialPos.y
                    }
                    if (this.options.drawRect) {
                        this.rect.left = this.bounds.x
                        this.rect.top = this.bounds.y
                        this.rect.width = this.bounds.width
                        this.rect.height = this.bounds.height
                        this.rect.dirty = true
                        fabricCanvas.requestRenderAllBound()
                    }
                },
                onMouseMove: function(e) {
                    if (!this.dragging || !this.freeDrawing) {
                        return
                    }
                    requestAnimationFrame(() => this.update(e.pointer))
                },
                onMouseUp: function(e) {
                    this.dragging = false;
                    if (!this.freeDrawing) {
                        return
                    }
                    if (this.options.drawRect && this.rect && (this.rect.width == 0 || this.rect.height === 0)) {
                        fabricCanvas.remove(this.rect)
                    }
                    if (!this.options.drawRect || !this.rect) {
                        this.rect = new fabric.Rect({
                            ...this.bounds,
                            left: this.bounds.x,
                            top: this.bounds.y,
                            ...this.options.rectProps
                        });

                        fabricCanvas.add(this.rect)
                        this.rect.dirty = true
                        fabricCanvas.requestRenderAllBound()

                    }

                    this.rect.setCoords() // important! 
                },
                install: function() {
                    this.freeDrawing = true;
                    this.dragging = false;
                    this.rect = null
                    this.checkDrawing = true;
                    this.mouseDown = function(e) {
                        modeDraw.onMouseDown(e)
                    }
                    this.mouseMove = function(e) {
                        modeDraw.onMouseMove(e)
                    }
                    this.mouseUp = function(e) {
                        modeDraw.onMouseUp(e)
                    }
                    fabricCanvas.on('mouse:down', this.mouseDown);
                    fabricCanvas.on('mouse:move', this.mouseMove);
                    fabricCanvas.on('mouse:up', this.mouseUp);
                },
                uninstall: function() {
                    this.freeDrawing = false;
                    this.dragging = false;
                    this.rect = null
                    this.checkDrawing = false
                    fabricCanvas.off('mouse:down', this.mouseDown);
                    fabricCanvas.off('mouse:move', this.mouseMove);
                    fabricCanvas.off('mouse:up', this.mouseUp);
                }
            }

            // chức năng đổ màu
            fillColor = {
                codeColor: '',
                opacity: 0.65,
                onMouseMove: function(e) {
                    let targetsHover = e.target
                    if (targetsHover) {
                        targetsHover.set('hoverCursor', 'cell');
                        targetsHover.drapable = false;
                    }

                },
                onMouseDown: function(e) {
                    let targetsHover = e.target

                    if (targetsHover) {
                        if (targetsHover._objects) {
                            targetsHover._objects.forEach(element => {
                                element.set('fill', this.codeColor)
                                element.set('opacity', this.opacity)
                            });
                        } else {
                            targetsHover.set('fill', this.codeColor)
                            targetsHover.set('opacity', this.opacity)
                        }

                    }


                },
                onSelectColor: function(e) {
                    var valueColor = $(e.currentTarget).val();
                    this.codeColor = valueColor || 'rgba(0,0,0,0.01)';
                },
                install: function() {
                    this.codeColor = 'rgba(0,0,0,0.01)';
                    this.mouseDown = function(e) {
                        fillColor.onMouseDown(e)
                    }
                    this.mouseMove = function(e) {
                        fillColor.onMouseMove(e)
                    }
                    this.selectColor = function(e) {
                        fillColor.onSelectColor(e)
                    }
                    fabricCanvas.on('mouse:move', this.mouseMove);
                    fabricCanvas.on('mouse:down', this.mouseDown);
                    $('#sel_color').on('change', this.selectColor);

                },
                uninstall: function() {
                    this.codeColor = 'rgba(0,0,0,0.01)';
                    fabricCanvas.off('mouse:move', this.mouseMove);
                    fabricCanvas.off('mouse:down', this.mouseDown);
                    $('#sel_color').off('change', this.selectColor);
                    fabricCanvas.getObjects().forEach((element) => {
                        element.set('hoverCursor', 'move');
                    })
                }
            }

            listMode = {
                modeDraw,
                fillColor,
                drawPolygon
            }

            var mode = false

            $('.js-select-mode').click(function() {
                var valMode = $(this).data('mode');

                mode && mode.uninstall();
                mode = listMode[valMode];
                mode && mode.install();
            })

            // fabricCanvas.on('selection:created', function(event) {
            //     var selected = event.selected;
            // })

            window.addEventListener("keyup", function(event) {
                if (event.keyCode === 27 || event.key === "Escape") {
                    $('.js-select-mode').first().click();
                }
                if (event.keyCode === 46 || event.key === "Delete") {
                    var activeObject = fabricCanvas.getActiveObject();
                    var activeGroup = false && fabricCanvas.getActiveGroup();
                    if (activeObject) {
                        if (confirm('Are you sure delete this?')) {
                            if (activeObject._objects) {
                                activeObject._objects.forEach(element => {
                                    fabricCanvas.remove(element)
                                });
                            }
                            fabricCanvas.remove(activeObject);
                        }
                    } else if (activeGroup) {
                        if (confirm('Are you sure delete this?')) {
                            var objectsInGroup = activeGroup.getObjects();
                            fabricCanvas.discardActiveGroup();
                            objectsInGroup.forEach(function(object) {
                                fabricCanvas.remove(object);
                            });
                        }
                    }
                }
            }, false);

            var imageUrl = "./img/imgbg.jpg";
            fabric.Image.fromURL(imageUrl, function(img) {
                img.scaleToWidth(fabricCanvas.width);
                //img.scaleToHeight(fabricCanvas.height);
                fabricCanvas.setHeight(img.getScaledHeight());
                fabricCanvas.setBackgroundImage(img);
                fabricCanvas.requestRenderAll();
            });



            fabricCanvas.on('mouse:over', function(e) {
                if (e.target) {
                    var mousePos = fabricCanvas.getPointer(e.e);
                    hoverTarget = fabricCanvas.findTarget(e, true);
                    if (e.target._objects) {
                        e.target._objects.forEach((ele) => {
                            ele.set('stroke', '#ff0');

                        })

                    } else {
                        e.target.set('stroke', '#ff0');
                    }
                    //fabricCanvas.setActiveObject(e.target)
                    fabricCanvas.renderAll();
                }
            })

            fabricCanvas.on('mouse:out', function(e) {
                if (e.target) {
                    if (e.target._objects) {
                        e.target._objects.forEach(element => {
                            element.set('stroke', '#333');
                        });
                    } else {
                        e.target.set('stroke', '#333');
                    }
                }
                fabricCanvas.renderAll();
            })


            var selectColor = $('#sel_color');

            $.ajax({
                url: 'ajax/ajax.php',
                method: "GET",
                dataType: 'json',
                data: {
                    act: 'mau'
                },
            }).done(function(result) {
                for (const item of result) {
                    let optItem = $('<option></option>');
                    optItem.val(item.code);
                    optItem.text(item.ten);
                    selectColor.append(optItem);
                }
            })

            $('.js-export-data').click(function() {
                $('.json-export-contain').text(JSON.stringify(fabricCanvas.toJSON()))
            })

            $('.js-import-data').click(function() {
                $.ajax({
                    url: 'ajax/ajax.php',
                    method: "GET",
                    dataType: 'json',
                    data: {
                        act: 'data'
                    },
                }).done(function(result) {
                    fabricCanvas.loadFromJSON(result, fabricCanvas.renderAll.bind(fabricCanvas), function(o, object) {
                        // `o` = json object
                        // `object` = fabric.Object instance
                        // ... do some stuff ...
                    });
                })

            })

        }())
    </script>
</body>

</html>