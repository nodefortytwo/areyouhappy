<div class="row-fluid">
    <div class="span12">
        <h1>{{page_title}}</h1>
    </div>
</div>
<form method="POST" action="{{form_action}}" enctype="multipart/form-data">
    <div class="row-fluid">
        <div class="span12">
            {{form_intro}}
        </div>
    </div>
    <div class="row-fluid">
        <div class="span8">
            <div class="row-fluid">
                <div class="span12">
                    <h2><small>Create a Chrncl Account</small></h2>
                </div>
            </div>
            <div class="row-fluid">
                <div class="span6">
                    <div class="controls controls-row">
                        {{form_pseudonym}}
                    </div>
                    <div class="controls controls-row">
                        {{form_email}}
                    </div>
                </div>
                <div class="span6">
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
        </div>
        <div class="span4">
            <div class="row-fluid">
                <div class="span12">
                    <h2><small>Or Use a Social Login</small></h2>
                </div>
            </div>
            <div class="row-fluid">
                <div class="span12">
                    {{register_facebook}}
                </div>
            </div>
            <div class="row-fluid">
                <div class="span12">
                    <a class="span10 btn btn-info btn-large btn-social"  href="#">Sign in with Twitter</a>
                </div>
            </div>
            <div class="row-fluid">
                <div class="span12">
                    <a class="span10 btn btn-danger btn-large btn-social"  href="#">Sign in with Google+</a>
                </div>
            </div>
        </div>
    </div>
</form>