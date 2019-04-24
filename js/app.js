var GlobalTableData = {};
var GlobalTableMarkup = {};
var tableSelected = {
    '1': false,
    '2': false
};

function exportCSV(data){

}

function openSlider(id){
    $('#'+id+'Div').css('display', 'block');
}

function closeSlider(id){
    $('#'+id+'Div').css('display', 'none');
}

function allowDrop(event){
    event.preventDefault();
}

function drag(event){
    event.dataTransfer.setData("text", event.target.id);
}

function drop(ev, content) {
    ev.preventDefault();
    var data=ev.dataTransfer.getData("text");
    var nodeCopy = document.getElementById(data).cloneNode(true);
    nodeCopy.id = "newId";
    ev.target.innerHTML = content;
    $('#'+ev.target.id+'Input').val('1');
}

function addColumns(data, id){
    $.each(GlobalTableData, function () {
       if(this.table === data){
           tableSelected[id] = true;
           var colMark = '';
           $.each(this.columns, function () {
              colMark += '<option value="'+this+'">'+this+'</option>';
           });
           $('#pkTable'+id).html(colMark);
           GlobalTableMarkup[id] = colMark;
       }
    });

    if(tableSelected['1'] === true && tableSelected['2'] === true){
        const allFields = GlobalTableMarkup['1'] + GlobalTableMarkup['2'];
        $('#sortField').html(allFields);
    }
}

function selectTable(ev, id){
    ev.preventDefault();
    var data=ev.dataTransfer.getData("text");
    var nodeCopy = document.getElementById(data).cloneNode(true);
    nodeCopy.id = "newId";
    ev.target.innerHTML = data;
    $('#'+ev.target.id+'Input').val(data);
    addColumns(data, id);
}

function createCSVFlow(responseJson){
    const drag = '<div id="csvFilename" class="btn" draggable="true" ondragstart="drag(event)">'+responseJson.filename+'</div>';

    const visualizer = '';
    $('#sourceData').html(drag);
    $('#fileName').val(responseJson.filename);
}

function createMySQLFlow(responseJson){
    const schema = responseJson.schema;
    GlobalTableData = schema;
    var markup = '<p>Database: <b>Busigence</b></p>';
    $.each(schema, function(){
        markup += '<a class="btn col s12" id="'+this.table+'" href="#" draggable="true" ondragstart="drag(event)">'+ this.table +'</a><br>';
    });
    $('#sourceData').html(markup);
}

function hideSource(source){
    if(source === 'csv'){
        $('#csvUploadDiv').html('Uploaded CSV File Successfully.');
        $('#dbConnectDiv').html('You have uploaded a CSV file, to connect to MySQL Database refresh the page.');
    } else{
        $('#dbConnectDiv').html('Connected to MySQL Host Successfully.');
        $('#csvUploadDiv').html('You have logged in to MySQL Database, to upload a CSV file refresh the page.');
    }
}

const toastText = {
    ".csvUpload": "Uploading File",
    ".dbConnect": "Logging in.",
    ".visualizeCSV": "Creating Preview",
    ".visualizeMySQL": "Creating Preview"
};

class Input{
    constructor(id){
        this._id = id;
        this._dom = $('input#'+id);
        this._errorField = $('#'+id+'_error');
        this._name = this._dom.attr('name');
    }

    add_error(messageArray){
        var message = "";
        $.each(messageArray, function (key, element) {
            message += element+"<br>";
        });
        this._errorField.append(message);
        this._errorField.css('display', 'block');
    }

    reset_error(){
        this._errorField.html('');
        this._errorField.css('display','none');
    }

    get name(){
        return this._name;
    }
}


class Form{
    constructor(cls){

        this._submissionUrl = "inc/"+cls+".php";
        cls = '.' + cls;
        this._class = cls;
        this._form = $('form'+cls);
        this._inputs = this.generate_inputs();
        this._submitBtn = $(cls+'SubmitButton');
        this._defaultBtnText = this.submitBtn.html();
    }

    generate_inputs(){
        var inputs = [];
        const dom = $(this._class+' :input');
        $.each(dom, function () {
            if(this.id){
                const inputDom = new Input(this.id);
                inputs.push(inputDom);
            }
        });
        return inputs;
    }

    reset_form_errors(){
        $.each(this._inputs, function () {
            this.reset_error();
        });
    }

    add_errors(error){   //error argument is default django form validation error response: form.errors
        $.each(this._inputs, function () {
            if(error.hasOwnProperty(this.name)){
                this.add_error(error[this.name]);
            }
        });
    }

    get_data(){
        return new FormData(this.form[0]);
    }

    update_btn_text(text){
        this.submitBtn.html(text);
    }

    submit(event){
        event.preventDefault();
        this.reset_form_errors();
        this.update_btn_text('Validating Information');
        const form = this;
        // Initialize a toast for form action.
        M.toast({html: toastText[form._class], displayLength: 5000});

        const btnID = form.form[0].id;

        if(btnID === 'visualizeMySQL'){
            $('#visualizeMySQLSubmit').html('Mapping Data...');
        } else if(btnID === 'csvUpload'){
            $('#csvUploadSubmit').html('Uploading CSV...');
        }

        $.ajax({
            url: this.submissionUrl,
            method: "post",
            data: this.get_data(),
            cache: false,
            contentType: false,
            processData: false,
            success: function(response){
                form.submission_success(JSON.parse(response));
            },
            error: function (message) {
                const data = JSON.parse(message);
                form.log_error(data);
            }
        });

        if(btnID === 'visualizeMySQL'){
            const output = $('#outputType').val();
            const data = form.form.serialize();
            if(output === 'csv'){
                window.open('./inc/exportCSV.php?'+data, '_blank');
            } else if(output === 'mysql'){
                M.toast({html: 'Importing Data into MySQL Table.', displayLength: 10000});

                $.when($.ajax('./inc/importMySQL.php?'+data)).then(function (response){ //Success Function
                    M.Toast.dismissAll();
                    M.toast({html: response});
                }, function(response){  // Error Function
                    alert(response);
                    console.log(response);
                });
            }
            $('#visualizeMySQLSubmit').html('Run The Mapping');
        } else if(btnID === 'csvUpload'){
            $('#csvUploadSubmit').html('Upload');
        }
    }

    submission_success(response){
        M.Toast.dismissAll();
        if(response.hasOwnProperty('message')){
            M.toast({html: response['message']});
        }
        if(response.hasOwnProperty('status')) {
            const status = response.status;
            if (status === true) {
                hideSource(response.source);
                this.form[0].reset();

                if (response.source === "csv") {
                    $("#sourceDataLink").html('CSV');
                    $("#visualizerLink").html('CSV');
                    $("#sourceData").html(response.filename + ' Uploaded.');
                    $('#defaultVisualizer').hide();
                    $('#csvVisualizer').css('display', 'block');
                    createCSVFlow(response);
                } else if (response.source === 'mysql') {
                    $("#sourceDataLink").html('MySQL');
                    $("#visualizerLink").html('MySQL');
                    $("#sourceData").html('Logged into the MySQL host.');
                    $('#defaultVisualizer').hide();
                    $('#mysqlVisualizer').css('display', 'block');
                    createMySQLFlow(response);
                } else if (response.source === 'csvVisualizer'){
                    $('#slider').html(response.markup);
                    openSlider('slider');
                } else if(response.source === 'mysqlVisualizer'){
                    $('#slider').html(response.markup);
                    openSlider('slider');
                }
            }
        } else{
            console.log(response);
        }
        if(response.hasOwnProperty('redirect_url')){
            window.open(response['redirect_url'], '_self');
        }
    }

    log_error(message){
        this.update_btn_text('Server Error. Please Try Again Later.');
        alert(message);
        console.log(message);
    }

    get form(){
        return this._form;
    }

    get class(){
        return this._class;
    }

    get inputs(){
        return this._inputs;
    }

    get submitBtn(){
        return this._submitBtn;
    }

    get defaultBtnText(){
        return this._defaultBtnText;
    }

    get submissionUrl(){
        return this._submissionUrl;
    }
}

$(document).ready(function(){
    // Initialize forms
    const forms = $('form');
    $.each(forms, function () {
        const formClass = this.getAttribute('class');
        let form = new Form(formClass);

        form.form.submit(function (event) {
            event.preventDefault();
            form.submit(event);
        });
    });

    // Initialize Tabs
    $('.tabs').tabs();

    // Initialize Dropdowns
    $('.dropdown-trigger').dropdown();


    $('select').formSelect();
});
