@php
    $data_for_img = $field->getState();
    $base64Image = $data_for_img['base64Image'];
    $entities = $data_for_img['entities']; // JSON result containing 'mentionText' and 'absoluteVertices'
    $originalWidth = $data_for_img['imageWidth'];
    $originalHeight = $data_for_img['imageHeight'];
@endphp

<div style="position: relative;">
    <div id="image-container" style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);">
        <img id="image" src="data:image/jpeg;base64,{{ $base64Image }}" alt="Processed Image" 
         style="width: 100%; height: auto;" />
        <svg id="overlay" style="position: absolute; top: 0; left: 0; pointer-events: none;"></svg>
    </div>
    <div id="legend" class="grid">
        <div class="legend-item">
            <div class="color-box red"></div>
            <p>Invoice Date</p>
        </div>
        <div class="legend-item">
            <div class="color-box green"></div>
            <p>Invoice ID</p>
        </div>
        <div class="legend-item">
            <div class="color-box blue"></div>
            <p>Line Item</p>
        </div>
        <div class="legend-item">
            <div class="color-box yellow"></div>
            <p>Supplier Name</p>
        </div>
    </div>

    <style>
        #legend {
            margin-top: 10px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }
        .legend-item {
            display: flex;
            align-items: center;
        }
        .color-box {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
        .red { background-color: rgba(255, 0, 0, 0.3); border: 2px solid red; }
        .green { background-color: rgba(0, 255, 0, 0.3); border: 2px solid green; }
        .blue { background-color: rgba(0, 0, 255, 0.3); border: 2px solid blue; }
        .yellow { background-color: rgba(255, 255, 0, 0.3); border: 2px solid yellow; }
    </style>

</div>

<script>
    window.addEventListener("load", function () {
    const image = document.getElementById('image');
    console.log("image", image);
    const overlay = document.getElementById('overlay');
    console.log("overlay", overlay);

    // Original dimensions of the image from the backend
    const originalWidth = {{ $originalWidth }};
    console.log("originalWidth", originalWidth);
    const originalHeight = {{ $originalHeight }};
    console.log("originalHeight", originalHeight);

    // Entities with absolute vertices
    const entities = @json($entities);
    console.log("entities", entities);

    // Add an onload listener for the image
    image.onload = function () {
        const renderedWidth = image.clientWidth;
        const renderedHeight = image.clientHeight;
        console.log("renderedWidth", renderedWidth);
        console.log("renderedHeight", renderedHeight);

        // Update the overlay SVG to match the rendered size of the image
        overlay.setAttribute("width", renderedWidth);
        overlay.setAttribute("height", renderedHeight);
        overlay.style.width = `${renderedWidth}px`;
        overlay.style.height = `${renderedHeight}px`;

        const colors = {
                    'invoice_date' : {
                        fill: 'rgba(255, 0, 0, 0.3)',
                        stroke: 'red',
                        strokeWidth: 2
                    },
                    'invoice_id' : {
                        fill: 'rgba(0, 255, 0, 0.3)',
                        stroke: 'green',
                        strokeWidth: 2
                    },
                    'line_item' : {
                        fill: 'rgba(0, 0, 255, 0.3)',
                        stroke: 'blue',
                        strokeWidth: 2
                    },
                    'supplier_name' : {
                        fill: 'rgba(255, 255, 0, 0.3)',
                        stroke: 'yellow',
                        strokeWidth: 2
                    }
                }

        // Scale and draw the polygons
        entities.forEach(entity => {
            console.log("entity", entity);
            if (entity.absoluteVertices) {
                const scaledPoints = entity.absoluteVertices.map(vertex => {
                    const scaledX = vertex.x * (renderedWidth / originalWidth);
                    const scaledY = vertex.y * (renderedHeight / originalHeight);
                    return `${scaledX},${scaledY}`;
                }).join(" ");

                const type = entity.type;
                

                const polygon = document.createElementNS("http://www.w3.org/2000/svg", "polygon");
                polygon.setAttribute("points", scaledPoints);
                polygon.setAttribute("style", `fill: ${colors[type].fill}; stroke: ${colors[type].stroke}; stroke-width: ${colors[type].strokeWidth};`);
                overlay.appendChild(polygon);
            }
        });
    };

    // Trigger onload manually in case the image is already loaded
    if (image.complete) {
        image.onload();
    }
});

</script>
