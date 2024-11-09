let station_data = null;
let chart = null;

let chartBackgroundColor = 'rgba(155,208,245,0.4)';
let chartBorderColor = '#36A2EB';

let last_selected_mode = 0;

async function fetch_station(){

    let chipid = new URLSearchParams(window.location.search).get("chipid");

    if(chipid === null || chipid === ""){
        station_alias.textContent = "Usted no ha seleccionado ninguna estación";
        return;
    }

    let response = await fetch("https://mattprofe.com.ar/proyectos/app-estacion/datos.php?chipid=" + chipid + "&cant=7");

    response = await response.json();

    if(response.errno === "2"){
        station_alias.textContent = "Estación no existente";
        return;
    }

    return response.reverse();

}



function setStationData(station_data){

    station_alias.textContent = station_data.estacion;
    station_location.textContent = station_data.ubicacion;

}



function getFireDanger(fwi){

    let fwi_float = parseFloat(fwi)

	if(fwi_float >= 50){
		return "Extremo"
	}

    if(fwi_float >= 38){
		return "Muy alto"
	}

    if(fwi_float >= 21.3){
		return "Alto"
	}

    if(fwi_float >= 11.2){
		return "Moderado"
	}

    if(fwi_float >= 5.2){
		return "Bajo"
	}

    return "Muy bajo";

}



function setButtonsData(station_data){

    button_temperature.textContent = station_data.temperatura.split(".")[0] + "C°";
    button_fire.textContent = getFireDanger(station_data.fwi);
    button_humidity.textContent = station_data.humedad.split(".")[0] + "%";
    button_pression.textContent = station_data.presion.split(".")[0] + "hPa";
    button_wind_direction.textContent = station_data.veleta;
    button_wind_speed.textContent = station_data.viento.split(".")[0] + "Km/H";

}



function setOptionData(station_data, mode){

    const last_position = station_data[station_data.length - 1];
    let container = null;

    switch (mode){

        case 0:

            container = temperature_template.content.cloneNode(true).querySelector("div");

            // TEMPERATURA
            container.querySelector("#temperature_integer").textContent = last_position.temperatura.split(".")[0];
            container.querySelector("#temperature_decimal").textContent += last_position.temperatura.split(".")[1];
            container.querySelector("#temperature_minvalue").textContent = last_position.tempmin;
            container.querySelector("#temperature_maxvalue").textContent = last_position.tempmax;

            // SENSACIÓN
            container.querySelector("#sensation_integer").textContent = last_position.sensacion.split(".")[0];
            container.querySelector("#sensation_decimal").textContent += last_position.sensacion.split(".")[1];
            container.querySelector("#sensation_minvalue").textContent = last_position.sensamin;
            container.querySelector("#sensation_maxvalue").textContent = last_position.sensamax;

            break;

        case 1:

            container = fire_template.content.cloneNode(true).querySelector("div");

            container.querySelector("#ffmc").textContent = last_position.ffmc;
            container.querySelector("#dmc").textContent = last_position.dmc;
            container.querySelector("#dc").textContent = last_position.dc;

            container.querySelector("#isi").textContent = last_position.isi;
            container.querySelector("#bui").textContent = last_position.bui;
            container.querySelector("#fwi").textContent = last_position.fwi;

            break;

        case 2:

            container = humidity_template.content.cloneNode(true).querySelector("div");

            container.querySelector("#humidity_integer").textContent = last_position.humedad.split(".")[0];
            container.querySelector("#humidity_decimal").textContent += last_position.humedad.split(".")[1];

            break;

        case 3:

            container = pression_template.content.cloneNode(true).querySelector("div");

            container.querySelector("#pression_integer").textContent = last_position.presion.split(".")[0];
            container.querySelector("#pression_decimal").textContent += last_position.presion.split(".")[1];

            break;

        case 4:

            container = wind_template.content.cloneNode(true).querySelector("div");

            container.querySelector("#wind_integer").textContent = last_position.viento.split(".")[0];
            container.querySelector("#wind_decimal").textContent += last_position.viento.split(".")[1];
            container.querySelector("#wind_direction").textContent = last_position.veleta;

            break;

    }

    stats_info.innerHTML = "";
    stats_info.appendChild(container);

}



function setChart(station_data, mode = 0){

    console.log(station_data);

    /* MODOS: 
        - 0: TEMPERATURA,
        - 1: FUEGO,
        - 2: HUMEDAD,
        - 3: PRESIÓN,
        - 4: VIENTO
    */

    if(chart !== null){
        chart.destroy();
    }

    let data_labels = [];
    let data_array = [];

    switch (mode){

        case 0:

            data_labels = station_data.map(dataset => dataset.fecha.substr(10, 6));
            data_array = station_data.map(dataset => dataset.temperatura);

            chartBorderColor = "#ff8800";
            chartBackgroundColor = "#e6deb3";

            break;

        case 1:

            data_labels = station_data.map(dataset => dataset.fecha.substr(10, 6));
            data_array = station_data.map(dataset => dataset.fwi);

            chartBorderColor = "#ff573d";
            chartBackgroundColor = "#ffa27a";

            break;

        case 2:

            data_labels = station_data.map(dataset => dataset.fecha.substr(10, 6));
            data_array = station_data.map(dataset => dataset.humedad);

            chartBorderColor = "#1f8fff";
            chartBackgroundColor = "#d1edff";

            break;

        case 3:

            data_labels = station_data.map(dataset => dataset.fecha.substr(10, 6));
            data_array = station_data.map(dataset => dataset.presion);

            chartBorderColor = "#41ff2b";
            chartBackgroundColor = "#cfffc9";

            break;

        case 4:

            data_labels = station_data.map(dataset => dataset.fecha.substr(10, 6));
            data_array = station_data.map(dataset => dataset.viento);

            chartBorderColor = "#b3e6e5";
            chartBackgroundColor = "#e6ffff";

            break;

    }

    chart = new Chart(stats_chart, {

        type: 'line',
        data: {

            labels: data_labels,

            datasets: [{    
                data: data_array,
                borderWidth: 3,
                backgroundColor: chartBackgroundColor,
                borderColor: chartBorderColor,
                fill: true
            }]

        },
        options: {

            plugins: { legend: { display: false } },

            scales: { 
                y: {
                    min: 0,
                    ticks: {
                        precision: 0
                    }
                }
            }

        }

    });

}



function updateChart(station_data, mode){

    let data_labels = [];
    let data_array = [];

    switch (mode){

        case 0:

            data_labels = station_data.map(dataset => dataset.fecha.substr(10, 6));
            data_array = station_data.map(dataset => dataset.temperatura);
            break;

        case 1:

            data_labels = station_data.map(dataset => dataset.fecha.substr(10, 6));
            data_array = station_data.map(dataset => dataset.fwi);
            break;

        case 2:

            data_labels = station_data.map(dataset => dataset.fecha.substr(10, 6));
            data_array = station_data.map(dataset => dataset.humedad);
            break;

        case 3:

            data_labels = station_data.map(dataset => dataset.fecha.substr(10, 6));
            data_array = station_data.map(dataset => dataset.presion);
            break;

        case 4:

            data_labels = station_data.map(dataset => dataset.fecha.substr(10, 6));
            data_array = station_data.map(dataset => dataset.viento);
            break;

        default:
            console.error("Variable mode is not defined");
            break;

    }

    chart.data.labels = data_labels;
    chart.data.datasets[0].data = data_array;

    chart.update();

}



document.addEventListener("DOMContentLoaded", async () => {

    station_data = await fetch_station();

    setStationData(station_data[0]);
    setButtonsData(station_data[station_data.length - 1]);
    setOptionData(station_data, 0);
    setChart(station_data);

    button_temperature.onclick = function(){
        if(last_selected_mode !== 0){

            setOptionData(station_data, 0);
            setChart(station_data, 0);
            last_selected_mode = 0;

        }
    }

    button_fire.onclick = function(){
        if(last_selected_mode !== 1){

            setOptionData(station_data, 1);
            setChart(station_data, 1);
            last_selected_mode = 1;

        }
    }

    button_humidity.onclick = function(){
        if(last_selected_mode !== 2){

            setOptionData(station_data, 2);
            setChart(station_data, 2);
            last_selected_mode = 2;

        }
    }

    button_pression.onclick = function(){
        if(last_selected_mode !== 3){

            setOptionData(station_data, 3);
            setChart(station_data, 3);
            last_selected_mode = 3;

        }
    }

    button_wind.onclick = function(){
        if(last_selected_mode !== 4){

            setOptionData(station_data, 4);
            setChart(station_data, 4);
            last_selected_mode = 4;

        }
    }

    setInterval( async () => {

        station_data = await fetch_station();

        setOptionData(station_data, last_selected_mode);
        updateChart(station_data, last_selected_mode);

    }, 60000);

});