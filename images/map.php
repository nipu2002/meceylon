<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sri Lanka Spice Harvest Calendar Map</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f7f0;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        /* Map Section */
        .map-section {
            flex: 2;
            position: relative;
        }

        #map {
            height: 100%;
            width: 100%;
        }

        /* Info Panel */
        .info-panel {
            flex: 1;
            background: white;
            box-shadow: -2px 0 10px rgba(0,0,0,0.1);
            overflow-y: auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #1a5f2b 0%, #0d3b1a 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        .header h1 {
            font-size: 22px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 12px;
            opacity: 0.9;
        }

        /* Spice List */
        .spice-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .spice-card {
            background: #f9f9f9;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            border-left: 5px solid #ff9800;
        }

        .spice-card:hover {
            transform: translateX(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .spice-card.active {
            background: #fff8e7;
            border-left-color: #4caf50;
        }

        .spice-name {
            font-size: 18px;
            font-weight: bold;
            color: #1a5f2b;
            margin-bottom: 10px;
        }

        .spice-name span {
            font-size: 24px;
            margin-right: 8px;
        }

        /* Season Chart */
        .season-chart {
            margin: 10px 0;
        }

        .month-row {
            display: flex;
            align-items: center;
            margin: 5px 0;
            gap: 10px;
        }

        .month-label {
            width: 40px;
            font-size: 11px;
            font-weight: bold;
            color: #666;
        }

        .harvest-bar {
            flex: 1;
            height: 25px;
            background: #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
        }

        .harvest-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff9800, #ff5722);
            border-radius: 12px;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 8px;
            color: white;
            font-size: 10px;
            font-weight: bold;
        }

        .planting-fill {
            height: 100%;
            background: linear-gradient(90deg, #4caf50, #8bc34a);
            border-radius: 12px;
            transition: width 0.3s;
        }

        .legend {
            display: flex;
            gap: 15px;
            margin: 15px 0;
            padding: 10px;
            background: #f0f0f0;
            border-radius: 8px;
            font-size: 11px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
        }

        .region-info {
            margin-top: 15px;
            padding: 12px;
            background: #e8f5e9;
            border-radius: 8px;
            font-size: 12px;
            line-height: 1.6;
        }

        .region-info strong {
            color: #1a5f2b;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .map-section {
                height: 50vh;
            }
            .info-panel {
                height: 50vh;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="map-section">
            <div id="map"></div>
        </div>
        
        <div class="info-panel">
            <div class="header">
                <h1>🇱🇰 Sri Lanka Spice Harvest Calendar</h1>
                <p>Click on any spice to see harvesting seasons by region</p>
            </div>
            
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background: #4caf50;"></div>
                    <span>Planting Season</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: #ff9800;"></div>
                    <span>Harvest Season</span>
                </div>
            </div>
            
            <div class="spice-list" id="spiceList"></div>
            
            <div class="region-info" id="regionInfo">
                <strong>📍 Growing Regions:</strong><br>
                • Cinnamon: Gampaha, Matale, Kalutara<br>
                • Pepper: Matale, Kandy, Ratnapura<br>
                • Cardamom: Kandy, Matale, Nuwara Eliya<br>
                • Clove: Kurunegala, Matale<br>
                • Turmeric: Badulla, Kurunegala, Matale<br>
                • Ginger: Matale, Kurunegala, Badulla
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Initialize Sri Lanka map
        const map = L.map('map').setView([7.8731, 80.7718], 7.5);
        
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; Sri Lanka Spice Map',
            subdomains: 'abcd',
            maxZoom: 19
        }).addTo(map);

        // Spice data with regions and harvest seasons
        const spices = [
            {
                name: 'Cinnamon',
                icon: '🌳',
                color: '#ff9800',
                regions: [
                    { name: 'Gampaha', lat: 7.0897, lng: 79.9995, season: 'Aug-Oct' },
                    { name: 'Matale', lat: 7.4678, lng: 80.6234, season: 'Aug-Oct' },
                    { name: 'Kalutara', lat: 6.5833, lng: 79.9667, season: 'Sep-Nov' }
                ],
                planting: [4, 5, 6], // Months: Apr-Jun
                harvest: [8, 9, 10]   // Months: Aug-Oct
            },
            {
                name: 'Black Pepper',
                icon: '🫑',
                color: '#795548',
                regions: [
                    { name: 'Matale', lat: 7.4761, lng: 80.6685, season: 'Nov-Jan' },
                    { name: 'Kandy', lat: 7.2906, lng: 80.6337, season: 'Nov-Jan' },
                    { name: 'Ratnapura', lat: 6.7055, lng: 80.3847, season: 'Dec-Feb' }
                ],
                planting: [4, 5, 6],
                harvest: [11, 0, 1] // Nov-Jan
            },
            {
                name: 'Cardamom',
                icon: '🌰',
                color: '#4caf50',
                regions: [
                    { name: 'Kandy', lat: 7.2906, lng: 80.6337, season: 'Sep-Nov' },
                    { name: 'Matale', lat: 7.4678, lng: 80.6234, season: 'Sep-Nov' },
                    { name: 'Nuwara Eliya', lat: 6.9497, lng: 80.7891, season: 'Oct-Dec' }
                ],
                planting: [3, 4, 5],
                harvest: [9, 10, 11] // Sep-Nov
            },
            {
                name: 'Clove',
                icon: '🌸',
                color: '#9c27b0',
                regions: [
                    { name: 'Kurunegala', lat: 7.4865, lng: 80.3650, season: 'Oct-Dec' },
                    { name: 'Matale', lat: 7.4678, lng: 80.6234, season: 'Oct-Dec' }
                ],
                planting: [6, 7, 8],
                harvest: [10, 11, 0] // Oct-Dec
            },
            {
                name: 'Turmeric',
                icon: '🌿',
                color: '#ffc107',
                regions: [
                    { name: 'Badulla', lat: 6.9934, lng: 81.0556, season: 'Aug-Oct' },
                    { name: 'Kurunegala', lat: 7.4865, lng: 80.3650, season: 'Aug-Oct' },
                    { name: 'Matale', lat: 7.4678, lng: 80.6234, season: 'Aug-Oct' }
                ],
                planting: [3, 4, 5],
                harvest: [8, 9, 10] // Aug-Oct
            },
            {
                name: 'Ginger',
                icon: '🌱',
                color: '#8bc34a',
                regions: [
                    { name: 'Matale', lat: 7.5123, lng: 80.6452, season: 'Jul-Sep' },
                    { name: 'Kurunegala', lat: 7.4865, lng: 80.3650, season: 'Jul-Sep' },
                    { name: 'Badulla', lat: 6.9934, lng: 81.0556, season: 'Aug-Oct' }
                ],
                planting: [2, 3, 4],
                harvest: [7, 8, 9] // Jul-Sep
            }
        ];

        let markers = [];
        let currentSpice = null;

        // Month names
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // Generate season chart HTML
        function generateSeasonChart(plantingMonths, harvestMonths) {
            let html = '<div class="season-chart">';
            
            for(let i = 0; i < 12; i++) {
                let plantingClass = plantingMonths.includes(i) ? 'planting-fill' : '';
                let harvestClass = harvestMonths.includes(i) ? 'harvest-fill' : '';
                let width = '100%';
                
                if(plantingMonths.includes(i) && harvestMonths.includes(i)) {
                    // Both planting and harvest in same month (rare)
                    html += `
                        <div class="month-row">
                            <div class="month-label">${monthNames[i]}</div>
                            <div class="harvest-bar">
                                <div class="harvest-fill" style="width: 50%; background: linear-gradient(90deg, #ff9800, #ff5722);">🌾</div>
                                <div class="planting-fill" style="width: 50%; background: #4caf50; position: relative; top: -25px;">🌱</div>
                            </div>
                        </div>
                    `;
                } else if(plantingMonths.includes(i)) {
                    html += `
                        <div class="month-row">
                            <div class="month-label">${monthNames[i]}</div>
                            <div class="harvest-bar">
                                <div class="planting-fill" style="width: 100%;">🌱</div>
                            </div>
                        </div>
                    `;
                } else if(harvestMonths.includes(i)) {
                    html += `
                        <div class="month-row">
                            <div class="month-label">${monthNames[i]}</div>
                            <div class="harvest-bar">
                                <div class="harvest-fill" style="width: 100%;">🌾</div>
                            </div>
                        </div>
                    `;
                } else {
                    html += `
                        <div class="month-row">
                            <div class="month-label">${monthNames[i]}</div>
                            <div class="harvest-bar">
                                <div style="width: 100%; height: 100%;"></div>
                            </div>
                        </div>
                    `;
                }
            }
            
            html += '</div>';
            return html;
        }

        // Clear markers from map
        function clearMarkers() {
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];
        }

        // Show regions for selected spice
        function showSpiceRegions(spice) {
            clearMarkers();
            
            // Add markers for each region
            spice.regions.forEach(region => {
                const icon = L.divIcon({
                    className: 'spice-marker',
                    html: `<div style="background-color: ${spice.color}; width: 12px; height: 12px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.3);"></div>`,
                    iconSize: [18, 18],
                    popupAnchor: [0, -9]
                });
                
                const popupContent = `
                    <div style="padding: 5px;">
                        <h3 style="color: #1a5f2b;">${region.name}</h3>
                        <p><strong>${spice.icon} ${spice.name}</strong></p>
                        <p><strong>🌾 Harvest Season:</strong> ${region.season}</p>
                        <p><strong>📍 Main Growing Area</strong></p>
                    </div>
                `;
                
                const marker = L.marker([region.lat, region.lng], { icon })
                    .bindPopup(popupContent)
                    .addTo(map);
                
                markers.push(marker);
            });
            
            // Fit bounds to show all markers
            if(markers.length > 0) {
                const group = L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.2));
            }
        }

        // Render spice list
        function renderSpiceList() {
            const container = document.getElementById('spiceList');
            
            container.innerHTML = spices.map((spice, index) => `
                <div class="spice-card" onclick="selectSpice(${index})" id="spice-card-${index}">
                    <div class="spice-name">
                        <span>${spice.icon}</span> ${spice.name}
                    </div>
                    ${generateSeasonChart(spice.planting, spice.harvest)}
                    <div style="font-size: 11px; color: #666; margin-top: 8px;">
                        📍 ${spice.regions.length} growing regions | 🌾 Harvest: ${getSeasonText(spice.harvest)}
                    </div>
                </div>
            `).join('');
        }

        // Get season text
        function getSeasonText(months) {
            const monthAbbr = months.map(m => monthNames[m]);
            if(monthAbbr.includes('Dec') && monthAbbr.includes('Jan')) {
                return 'Nov-Feb';
            }
            return `${monthAbbr[0]}-${monthAbbr[monthAbbr.length-1]}`;
        }

        // Select spice function (global)
        window.selectSpice = function(index) {
            // Remove active class from all cards
            document.querySelectorAll('.spice-card').forEach(card => {
                card.classList.remove('active');
            });
            // Add active class to selected card
            document.getElementById(`spice-card-${index}`).classList.add('active');
            
            currentSpice = spices[index];
            showSpiceRegions(currentSpice);
            
            // Update region info
            const regionInfo = document.getElementById('regionInfo');
            regionInfo.innerHTML = `
                <strong>📍 ${currentSpice.icon} ${currentSpice.name} Growing Regions:</strong><br>
                ${currentSpice.regions.map(r => `• ${r.name}: ${r.season}`).join('<br>')}
                <br><br>
                <strong>🌱 Planting Season:</strong> ${getSeasonText(currentSpice.planting)}<br>
                <strong>🌾 Harvest Season:</strong> ${getSeasonText(currentSpice.harvest)}
            `;
        };

        // Add district boundaries overlay (simplified - showing major cities)
        const majorCities = [
            { name: 'Colombo', lat: 6.9271, lng: 79.8612, type: 'capital' },
            { name: 'Kandy', lat: 7.2906, lng: 80.6337, type: 'major' },
            { name: 'Galle', lat: 6.0328, lng: 80.2168, type: 'major' },
            { name: 'Matale', lat: 7.4678, lng: 80.6234, type: 'spice' },
            { name: 'Kurunegala', lat: 7.4865, lng: 80.3650, type: 'spice' },
            { name: 'Ratnapura', lat: 6.7055, lng: 80.3847, type: 'spice' },
            { name: 'Badulla', lat: 6.9934, lng: 81.0556, type: 'spice' },
            { name: 'Nuwara Eliya', lat: 6.9497, lng: 80.7891, type: 'spice' },
            { name: 'Gampaha', lat: 7.0897, lng: 79.9995, type: 'spice' },
            { name: 'Kalutara', lat: 6.5833, lng: 79.9667, type: 'spice' }
        ];

        // Add city markers to map
        majorCities.forEach(city => {
            const color = city.type === 'capital' ? '#ff4444' : (city.type === 'major' ? '#666' : '#ff9800');
            const icon = L.divIcon({
                html: `<div style="background-color: ${color}; width: 6px; height: 6px; border-radius: 50%; border: 2px solid white;"></div>`,
                iconSize: [10, 10],
                className: 'city-marker'
            });
            
            L.marker([city.lat, city.lng], { icon })
                .bindPopup(`<b>${city.name}</b><br>Spice Growing Region`)
                .addTo(map);
        });

        // Initialize
        renderSpiceList();
        
        // Auto-select first spice
        setTimeout(() => {
            selectSpice(0);
        }, 500);

        // Add title control
        const titleControl = L.control({ position: 'topright' });
        titleControl.onAdd = function() {
            const div = L.DomUtil.create('div', 'info-title');
            div.innerHTML = '<div style="background: white; padding: 8px 12px; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.2); font-size: 12px; font-weight: bold;">🇱🇰 Sri Lanka Spice Regions</div>';
            return div;
        };
        titleControl.addTo(map);
    </script>
</body>
</html>