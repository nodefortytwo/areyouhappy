<div class="row-fluid">
    <div class="span2">
       {{user_picture}}
    </div>
    <div class="span4">
        <div class="row-fluid">
            <div class="span12">
                <strong>Pseudonym:</strong>
                <br/>
                {{user_username}}
            </div>
        </div>
        <div class="row-fluid">
            <div class="span6">
                <strong>Firstname:</strong>
                <br/>
                {{user_firstname}}
            </div>
            <div class="span6">
                <strong>Lastname:</strong>
                <br/>
                {{user_lastname}}
            </div>
        </div>
        <div class="row-fluid">
            <div class="span6">
                <strong>Gender:</strong>
                <br/>
                {{user_gender}}
            </div>
            <div class="span6">
                <strong>Age:</strong>
                <br/>
                {{user_age}}
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12">
                <strong>Where do you like to write?</strong>
                <br/>
                {{user_location}}<br/>
                
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12">
                {{user_edit}}
            </div>
        </div>
    </div>
    <div class="span6">
        <div class="row-fluid">
            <div class="span12">
                <strong>About You:</strong>
                <br/>
                {{user_profile}}
            </div>
        </div>
    </div>
</div>
<div class="row-fluid">
    <div class="span6">
        <div class="row-fluid">
            <div class="span8">
                <h2>Your Stories</h2>
            </div>
            <div class="span4">
                <a href='{{story_create_url}}' class='pull-right btn'>Create New Story</a>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12">
                <p>
                    These are stories or books you have written:
                </p>
            </div>
        </div>
        <div class="row-fluid">
            <div class="span12">
                {{user_stories}}
            </div>
        </div>
    </div>
    <div class="span6">
        <h2>Story Stream</h2>
        <p>
            These are stories from authors you follow:
        </p>
    </div>
</div>
