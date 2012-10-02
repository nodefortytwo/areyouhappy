<div class="row-fluid">
    <div class="span12">
        <h1>{{page_title}}</h1>
    </div>
</div>
<form method="POST" action="{{form_action}}" enctype="multipart/form-data">
    <div class="row-fluid">
        <div class="span4">
            <div class="controls controls-row">
                {{form_pseudonym}}
            </div>
            <div class="controls controls-row">
                {{form_email}}
            </div>
            <div class="controls controls-row">
                {{form_picture}}
            </div>
        </div>
        <div class="span4">
            <div class="controls controls-row">
                {{form_firstname}}
            </div>
            <div class="controls controls-row">
                {{form_lastname}}
            </div>
            <div class="controls controls-row">
                {{form_age}}
            </div>
            <div class="controls controls-row">
                {{form_location}}
            </div>
        </div>
        <div class="span4">
            <div class="controls controls-row">
                {{form_profile}}
            </div>
            <div class="controls controls-row">
                {{form_gender}}
            </div>
            <div class="controls controls-row">
                {{form_password}}
            </div>
            <div class="controls controls-row">
                {{form_cpassword}}
            </div>
            <div class="controls controls-row">
                {{form_submit}}
            </div>
        </div>
    </div>
</form>