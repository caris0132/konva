<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <!-- <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script> -->
    <script src="assets/konva.min.js"></script>
    <title>Konva</title>
    <style>

    </style>
</head>

<body>
    <div id="container" class="container"></div>
    <script>
        (function() {
            var stage = new Konva.Stage({
                container: 'container',
                width: 700,
                height: 700,
            });

            var layer = new Konva.Layer();

            /*
             * create a triangle shape by defining a
             * drawing function which draws a triangle
             */

            var rubberArea = new Konva.Rect({
                x: 0,
                y: 0,
                width: 0,
                height: 0,
                stroke: 'red',
                dash: [2, 2]
            });
            rubberArea.listening(false);

            // add the triangle shape to the layer
            layer.add(rubberArea);

            // add the layer to the stage
            stage.on('mousemove', function(e) {
                var mousePos = stage.getPointerPosition();
            })
            stage.add(layer);

            var posStart;
            var posNow;
            var mode = '';

            function startDrag(posIn) {
                posStart = {
                    x: posIn.x,
                    y: posIn.y
                };
                posNow = {
                    x: posIn.x,
                    y: posIn.y
                };
            }

            function updateDrag(posIn) {
                posNow = {
                    ...posIn
                };
                posRect = reverse(posStart, posNow);
                rubberArea.x(posRect.x1);
                rubberArea.y(posRect.y1);
                rubberArea.width(posRect.x2 - posRect.x1);
                rubberArea.height(posRect.y2 - posRect.y1);
                rubberArea.visible(true);
                rubberArea.draw();
            }

            function reverse(r1, r2) {
                var r1x = r1.x,
                    r1y = r1.y,
                    r2x = r2.x,
                    r2y = r2.y,
                    d;
                if (r1x > r2x) {
                    d = Math.abs(r1x - r2x);
                    r1x = r2x;
                    r2x = r1x + d;
                }
                if (r1y > r2y) {
                    d = Math.abs(r1y - r2y);
                    r1y = r2y;
                    r2y = r1y + d;
                }
                return ({
                    x1: r1x,
                    y1: r1y,
                    x2: r2x,
                    y2: r2y
                }); // return the corrected rect.     
            }

            stage.on('mousedown', function(e) {
                mode = 'drawing';
                startDrag({
                    x: e.evt.layerX,
                    y: e.evt.layerY
                });
            })

            stage.on('mousemove', function(e) {
                if (mode === 'drawing') {
                    updateDrag({
                        x: e.evt.layerX,
                        y: e.evt.layerY
                    })
                }
            })

            stage.on('mouseup', function(e) {
                mode = '';
                rubberArea.visible(false);
                var newRect = new Konva.Rect({
                    x: rubberArea.x(),
                    y: rubberArea.y(),
                    width: rubberArea.width(),
                    height: rubberArea.height(),
                    fill: 'red',
                    listening: false
                })
                layer.add(newRect);
                stage.draw();

            })
        }())
    </script>
</body>

</html>