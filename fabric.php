<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
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
            <div class="col-md-3">
                <div class="btn btn-info btn-block">Chức năng</div>
                <select name="sel_mode" id="sel_mode" class="form-control">
                    <option value="" readonly>No mode</option>
                    <option value="modeDraw">Vẽ ô vuông</option>
                    <option value="fillColor">Tô màu</option>
                </select>
                <select class="form-control" id="sel_color">
                    <option value="" readonly>Chọn màu</option>
                </select>
            </div>
            <div class="col-md-6">
                <canvas name="canvas" id="canvas" width="540" height="500"></canvas>
            </div>
            <div class="col-md-3">
                <div class="btn btn-info btn-block">Chức năng</div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const canvas = document.querySelector('#canvas');
            let listMode = {}
            var fabricCanvas = new fabric.Canvas(canvas);


            const modeDraw = {
                dragging: false,
                freeDrawing: false,
                initialPos: false,
                rect: '',
                bounds: true,
                options: {
                    drawRect: true,
                    rectProps: {
                        stroke: '',
                        strokeWidth: 1,
                        strokeDashArray: [10, 5],
                        fill: '',
                    }
                },
                onMouseDown: function(e) {
                    console.log('a');
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
                    this.mouseDown = function (e) {
                        modeDraw.onMouseDown(e)
                    }
                    this.mouseMove = function (e) {
                        modeDraw.onMouseMove(e)
                    }
                    this.mouseUp = function (e) {
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
                        targetsHover.set('hoverCursor', 'cell')
                    }

                },
                onMouseDown: function(e) {
                    let targetsHover = e.target
                    if (targetsHover) {
                        targetsHover.set('fill', this.codeColor)
                        targetsHover.set('opacity', this.opacity)
                    }
                },
                onSelectColor: function(e) {
                    var valueColor = $(e.currentTarget).val();
                    this.codeColor = valueColor || '';
                },
                install: function() {
                    this.codeColor = '';
                    this.mouseDown = function (e) {
                        fillColor.onMouseDown(e)
                    }
                    this.mouseMove = function (e) {
                        fillColor.onMouseMove(e)
                    }
                    this.selectColor = function (e) {
                        fillColor.onSelectColor(e)
                    }
                    fabricCanvas.on('mouse:move', this.mouseMove);
                    fabricCanvas.on('mouse:down', this.mouseDown);
                    $('#sel_color').on('change', this.selectColor);

                },
                uninstall: function() {
                    this.codeColor = '';
                    fabricCanvas.off('mouse:move', this.mouseMove);
                    fabricCanvas.off('mouse:down', this.mouseDown);
                    $('#sel_color').off('change', this.selectColor);
                }
            }

            listMode = {
                modeDraw,
                fillColor
            }


            var selectMode = $('#sel_mode');
            var selectModeValue = selectMode.val();
            var mode = listMode[selectModeValue];
            mode && mode.install();

            fabricCanvas.on('selection:created', function(event) {
                var selected = event.selected;
            })

            selectMode.change(function() {
                var valMode = $(this).val();

                mode && mode.uninstall();
                mode = listMode[valMode];
                mode && mode.install();
            })

            window.addEventListener("keyup", function(event) {
                if (event.keyCode === 27 || event.key === "Escape") {
                    selectMode.val('');
                    selectMode.change();
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
                e.target && e.target.set('stroke', 'red');
                fabricCanvas.renderAll();
            })

            fabricCanvas.on('mouse:out', function(e) {
                e.target && e.target.set('stroke', '');
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

        }())
    </script>
</body>

</html>