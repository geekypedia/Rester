//WIDE.js Coded by Javi Agenjo (@tamat) 2018
"use strict"

//main class
var WIDE = {

    //config
    settings: {
        server_url: "server.php", //change it if it is hosted in a different folder than the index.html
        persistent_session: false, //set to false if you do not want the keys to be stored in localStorage
        plugins: []
    },

    //globals
    key: "",

	commands: {},
	files: [],
	files_by_name: {},
	current_file: null,
	visible_file: null,
	current_folder: ".",
	extensions_to_language: { "js":"javascript", "py":"python", "md": "markdown", "cs": "csharp", "hbs": "handlebars" },
	buttons: [],
    console_open: false,
    files_list_open: false,

	init: function()
	{
		this.container = document.getElementById('container');
		this.sidebar = document.getElementById('sidebar');
		this.editor_container = document.getElementById('code-editor');

		this.container.style.display = "none";
		require.config({ paths: { 'vs': 'js/monaco-editor/min/vs' }});
		require(['vs/editor/editor.main'], function(){
			document.getElementById('loader').style.display = "none";
			WIDE.container.style.display = "";
			WIDE.onReady();
		});

        this.editor_header = document.querySelector("#code-header");       

		var input = this.console_input = document.querySelector("#bottom input");
		input.addEventListener("keydown",function(e){
			//console.log(e.code,e.ctrlKey);
			if(e.keyCode == 13)
			{
				WIDE.onCommand( this.value );
                this.style.opacity = 1;
				this.value = "";
				return;
			}
			else if(e.keyCode == 9) //TAB
			{
				var last = this.value.split(" ").pop();
				WIDE.autocomplete( last, function(a,b){ input.value += b; });
				e.preventDefault();
				return;
			}
			else if( e.keyCode == 27 && WIDE.visible_file) //ESC 
			{
				WIDE.visible_file.editor.focus();
				e.preventDefault();
			}
            if(this.value.substr(0,4) == "key " || this.value.substr(0,8) == "tempkey ") //hide key
                this.style.opacity = 0;
            else
                this.style.opacity = 1;
		},true);

		document.addEventListener("keydown", this.onKey.bind(this), true );

		window.onresize = this.onResize.bind(this);
        window.onbeforeunload = function()
        {
			if(WIDE.settings.persistent_session){
				WIDE.saveSession();
			}
            if(!WIDE.checkAllFilesSaved())
                return "Not all files are saved";
            
        }

		var container = document.querySelector("#sidebar .header");
		for(var i in this.buttons)
		{
			var b = this.buttons[i];
			var element = document.createElement("button");
			if(b.icon)
				element.innerHTML = '<svg class="icon"><use xlink:href="#si-'+b.icon+'"/></svg>';
			else
				element.innerHTML = b.name;
			if(b.className)
				element.className = b.className;
			element.setAttribute("title",b.name);
			element.dataset["command"] = b.command;
			element.addEventListener("click",function(e){ WIDE.onCommand( this.dataset["command"], true ); });
			container.appendChild(element);
		}

        this.console_element = document.querySelector("#console");
	},

    onResize: function()
    {
        for(var i = 0; i < this.files.length; ++i)
            if(this.files[i].editor)
                this.files[i].editor.layout();
    },

    onReady: function()
    {
        WIDE.reset();
        WIDE.loadSession();
    },

	showCodeEditor: function( file_info, force_assign )
	{
		if(!file_info)
			return null;

        //editor already exist
		if(file_info.editor)
		{
			if(this.visible_file != file_info)
			{
				if(	this.visible_file )
					this.visible_file.editor_element.style.display = "none";
			}
			file_info.editor_element.style.display = "";
			this.visible_file = file_info;
			if(force_assign)
				file_info.editor.setValue(file_info.content);
			file_info.editor.layout();
			return file_info.editor;
		}

        //create container
		var editor_element = document.createElement("div");
		editor_element.classList.add("editor-wrapper");
		this.editor_container.appendChild( editor_element );
		file_info.editor_element = editor_element;

		//a model is a file content
		var model = monaco.editor.createModel("", "javascript");
		file_info.model = model;

		//an editor is a view of a model
		var editor = monaco.editor.create( editor_element, {
			value: "",
			model: model,
			language: 'javascript',
			//theme: 'vs-dark',
			theme: 'vs-light',
			folding: true
		});
		file_info.editor = editor;
		editor.file_info = file_info;

        //events
		setTimeout(function(){ editor.layout(); }, 1000);
		editor.onDidType( function(e){
			WIDE.onContentChange( file_info );
		});
		editor.onContextMenu(function(e){
		});
		editor.onKeyDown( function(e){
			WIDE.onEditorKey(e);
		});
		editor.addCommand(monaco.KeyCode.Escape, function() {
			//console.log('my command is executing!');
		});

        //hide previous editor
		if(	this.visible_file && this.visible_file != file_info )
			this.visible_file.editor_element.style.display = "none";
		this.visible_file = file_info;

        //update content and settings
		editor.setValue( file_info.content || "" );
        if( file_info.cursor )
            editor.setPosition( file_info.cursor );
		if( file_info.scrollTop )
			editor.setScrollTop( file_info.scrollTop );

		return editor;
	},

	load: function( filename, callback, open, focus )
	{
		var file_info = this.onFileAdded( filename );
		console.log("loading file: " + filename );

		if( !WIDE_SERVER.load( filename, inner_loaded, inner_error ) )
			return null;

		function inner_loaded( filename, data )
		{
			var file_info = WIDE.onFileLoaded( filename, data );
			if(callback)
				callback( filename, file_info );
			if( open )
				WIDE.open( filename, focus );
		}

		function inner_error( filename, err )
		{
			WIDE.close( file_info.name );
		}

        return file_info;
	},

	save: function()
	{
		if(!WIDE.current_file)
			return;

		var file_info = WIDE.current_file;
		file_info.content = file_info.editor.getValue();

		var filename = WIDE.current_file.name;
		var content = WIDE.current_file.content;

		if( !WIDE_SERVER.save( filename, content, inner_complete, inner_error ) )
			return;

        document.querySelector("#code-area").classList.add("saving");

		function inner_complete( filename )
		{
			document.querySelector("#code-area").classList.remove("saving");
			WIDE.onFileSaved( filename );
		}

		function inner_error()
		{
			document.querySelector("#code-area").classList.remove("saving");
			document.querySelector("#code-area").classList.add("error-saving");
			setTimeout( function(){ document.querySelector("#code-area").classList.remove("error-saving"); }, 1000 );
		}
	},

	"delete": function( filename )
	{
		if(!filename)
			return;

		var file_info = WIDE.files_by_name[filename];
		if(file_info && file_info._exist === false) //local file
		{
			this.close( filename );
			return;
		}

		if( !WIDE_SERVER.delete( filename, inner_complete) )
			return;

		function inner_complete( filename )
		{
            WIDE.close( filename );
		}
	},

	move: function( filename, new_filename )
	{
		if(!filename || !new_filename)
			return;

        if(!this.key) //keep this
        {
            console.error("file cannot be moved, no key set");
            return;
        }              

		var file_info = WIDE.files_by_name[filename];
		if(file_info)
			this.close( filename );

		if( !WIDE_SERVER.move( filename, new_filename, inner_complete ) )
			return;

		function inner_complete()
		{
             WIDE.list();
		};
	},

	open: function( filename, focus )
	{
        if(!filename)
            return; 
        if( this.current_file && this.current_file.name == filename )
            return;
        this.onVisibleFileChange();
		var file_info = this.files_by_name[ filename ];
		if(!file_info || file_info.content === null)
		{
			this.load( filename, null, true, focus );
			return;
		}
		this.current_file = file_info;
		this.showCodeEditor( file_info );

		file_info.file_element.classList.add("selected");
		var filename_pretty = filename.split("/").join("<span class='slash'>/</span>");
		this.editor_header.innerHTML = "<span class='filename'>" + filename_pretty + "</span><span class='close'>&#10005;</span>";
		this.editor_header.querySelector(".close").addEventListener("click",function(e){
			WIDE.close();
		});

        var index = filename.lastIndexOf(".");
        var ext = "text";
        if(index != -1)
            ext = filename.substr(index+1).toLowerCase();
		if( this.extensions_to_language[ext] )
			ext = this.extensions_to_language[ext];
        monaco.editor.setModelLanguage( file_info.model, ext );
        
        if(focus)
		    file_info.editor.focus();
	},

    close: function( filename )
    {
        var is_current = this.current_file && this.current_file.name == filename;
        if(!filename)
            filename = this.current_file ? this.current_file.name : null;
        if(!filename)
            return;
        var file_info = this.files_by_name[ filename ];
        if(!file_info)
            return;

		//remove filename from list
        file_info.file_element.parentNode.removeChild( file_info.file_element );
		//update numbers next to filename
        var entries = document.querySelectorAll("#open-files .filename .number");
        for(var i = 0; i < entries.length && i < 10; ++i)
            entries[i].innerHTML = i+1;
		//remove file from containers
        var index = this.files.indexOf( file_info );
        if(index != -1)
            this.files.splice( index, 1 );
        delete this.files_by_name[ filename ];
		if( file_info.onLeave )
			file_info.onLeave( file_info.editor ? file_info.editor.getValue() : null );
        //remove editor
		if(file_info.editor_element) //bin files do not have editor
        {
            file_info.editor.destroy();
			file_info.editor_element.parentNode.removeChild( file_info.editor_element );
        }
        
		//select previous file
        if(!is_current)
            return;
        var next_file = this.files[ index < this.files.length ? index : this.files.length - 1 ];
        if(!next_file)
		{
            this.current_file = null;
			var header = document.querySelector("#code-header");
			header.innerHTML = "";
		}
        else
            this.open( next_file.name );
    },

	create: function( filename, content, open )
	{
		if(!filename)
			filename = prompt("Choose file name");
		if(!filename)
			return;
        if( this.current_file && this.current_file.name == filename )
            return;
        this.onVisibleFileChange();
        var file_info = this.onFileAdded( filename, content || "" );
        file_info.file_element.classList.add("modified");
        file_info._exist = false;
        if(open)
            this.open(filename, true);
		return file_info;
	},


	createFolder: function( foldername)
	{
		if(!foldername)
			filename = prompt("Choose folder name");
		if(!foldername)
		if(!foldername)
			return;
		if( !WIDE_SERVER.saveFolder(WIDE.current_folder, foldername, inner_complete, inner_error ) )
			return;

		document.querySelector("#code-area").classList.add("saving");

		function inner_complete( foldername )
		{
			document.querySelector("#code-area").classList.remove("saving");
			//WIDE.onFileSaved( foldername );
			WIDE.list();
		}

		function inner_error()
		{
			document.querySelector("#code-area").classList.remove("saving");
			document.querySelector("#code-area").classList.add("error-saving");
			setTimeout( function(){ document.querySelector("#code-area").classList.remove("error-saving"); }, 1000 );
		}
	

        // if( this.current_file && this.current_file.name == filename )
        //     return;
        // this.onVisibleFileChange();
        // var file_info = this.onFileAdded( filename, content || "" );
        // file_info.file_element.classList.add("modified");
        // file_info._exist = false;
        // if(open)
        //     this.open(filename, true);
		//return file_info;
	},	

    reset: function()
    {
        this.files = [];
        this.files_by_name = {};
        this.current_file = null;
		document.querySelector("#open-files").innerHTML = "";
        document.querySelector("#code-editor").innerHTML = "";
    },

	list: function( folder, skip_log, on_complete )
	{
		folder = this.processFolder(folder);

		queryforEach("#sidebar .header button.sidebarmode",function(a){ a.classList.remove("selected"); });
		document.querySelector(".list-files-button").classList.add("selected");
		document.querySelector("#open-files").style.display = "none";
		var container = document.querySelector("#folder-files");
		container.style.display = "block";

        if(!this.key)
        {
        	var container = document.querySelector("#folder-files");
            container.innerHTML = "<p>Cannot access server, no key found.</p><p>Type your key to have access to the server.</p><p><input placeHolder='type key' type='password'/></p>";
			container.querySelector("input").addEventListener("keydown",function(e){
				if(e.keyCode != 13)
					return;
				WIDE.setKey(this.value);	
			});
            return;
        }

		if(!WIDE_SERVER.list( folder, false, inner_complete ))
			return;

		container.classList.add("loading");

		function inner_complete( folder, files, project ){
			WIDE.onShowFolderFiles( folder, files, project, skip_log );
			if(on_complete)
				on_complete(folder, files, project);
		}
	},

    autocomplete: function( filename, on_complete, folder )
    {
        folder = folder || this.current_folder;

        if(!this.key)
            return;

        var fullpath = this.cleanPath( folder + "/" + filename );

		if(!WIDE_SERVER.autocomplete( fullpath, inner_complete ))
			return;

		function inner_complete( data, shared )
		{
			if( on_complete )
				on_complete( data, shared );
		}
    },

    serialize: function()
    {
		if(this.current_file)
		{
			this.current_file.cursor = this.current_file.editor.getPosition();
			this.current_file.scrollTop = this.current_file.editor.getScrollTop();
		}

        var o = { 
			key: this.settings.persistent_session ? this.key : null, 
			files: [],
			current_folder: this.current_folder,
            settings: this.settings
		};
        for(var i = 0; i < this.files.length; ++i)
        {
            var file_info = this.files[i];
			if(!file_info.name || file_info.internal)
				continue;
			var item = { name: file_info.name, cursor: file_info.cursor, scrollTop: file_info.scrollTop };
            if(file_info._exist === false)
				item.content = file_info.editor ? file_info.editor.getValue() : file_info.content;
            o.files.push(item);
        }
        if(this.current_file)
            o.current = {
                name: this.current_file.name,
                cursor: this.current_file.editor.getPosition(),
				scrollTop: this.current_file.editor.getScrollTop()
            }
        return o;
    },

    configure: function(o)
    {
		if(o.key)
			this.key = o.key;

        if(o.settings)
        {
            this.settings = o.settings;
            if(this.settings.plugins && this.settings.plugins.length)
                this.loadPlugins();
        }
        
		if(o.current_folder)
			this.current_folder = o.current_folder;

        if(o.files)
            for(var i = 0; i < o.files.length; ++i)
            {
                var file_data = o.files[i];
				var file_info;
				if( file_data.content != null )
					file_info = this.create( file_data.name, file_data.content );
				else 
					file_info = this.load( file_data.name );
				if(!file_info) //no key
					continue;
				for(var j in file_data)
					file_info[j] = file_data[j];
            }
        if(o.current)
        {
			var file_info = null;
            if(o.current.name)
			{
				if( o.current.content )
	                file_info = this.create( o.current.name, o.current.content );
				else
		            file_info = this.open(o.current.name);
			}
			if( file_info )
			{
				if(o.current.cursor)
					file_info.editor.setPosition( o.current.cursor );
				if(o.current.scrollTop)
					file_info.editor.setScrollTop( o.current.scrollTop );
			}
        }
		if(this.current_file && this.current_file.editor)
			this.current_file.editor.focus();
    },

    clearSession: function()
    {
        localStorage.removeItem("wide_session");
    },
    
    loadSession: function()
    {
        var session = localStorage.getItem("wide_session");
        if(!session)
        {
            console.log("no session found, type: key YOURKEY");
            return;
        }
        session = JSON.parse( session );
        this.configure(session);
    },

    saveSession: function()
    {
        localStorage.setItem("wide_session", JSON.stringify( WIDE.serialize() ));
    },

    loadPlugins: function()
    {
        if(!this.settings.plugins)
            return;
        var old = document.querySelectorAll("script.plugin");
        for(var i = 0; i < old.length; ++i)
            old[i].parentNode.removeChild( old[i] );
        for(var i = 0; i < this.settings.plugins.length; ++i)
        {
            var script = document.createElement('script');
            script.src = this.settings.plugins[i];
            script.className = "plugin";
            document.head.appendChild( script );
        }
    },    
	
    checkAllFilesSaved: function()
    {
        for(var i = 0; i < this.files.length; ++i)
        {
            var file = this.files[i];
            if( file._exist === false || file.file_element.classList.contains("modifyed") )
               return false;
        }
        return true;
    },

	execute: function()
	{
        if(!this.current_file)
            return;
		var content = this.current_file.editor.getValue();
        if(this.current_file.onExecute)
            return this.current_file.onExecute(content);
		console.log("evaluating code");
        console.log("--------------------------");
		var func = new Function(content);
		var r = func.call(window);
	},

	setKey: function( key, temporal )
	{
		this.key = key;
		WIDE.commands.clear();
		if(this.key)
			this.list();
		if(temporal)
			WIDE.settings.persistent_session = false;
		console.log("key assigned");
	},

    //events
	onCommand: function( cmd, skip_console )
	{
        if(!skip_console)
		    this.toConsole( "PROMPT> " + cmd, "log me" );
        if(cmd[0] == "=") //eval JS
        {
            var r = eval(cmd.substr(1));
            console.log(String(r));
            return;
        }
		var t = cmd.split(" ");
		var func = this.commands[t[0]];
		if( !func )
		{
			console.error("command unknown: " + t[0]);
			return;
		}
		var r = func( cmd, t );
        if(r && r.constructor === String)
            console.log(r);
	},

    //this is executed before any other onKey event (even inputs or monaco-editor)
	onKey: function(e)
	{
		//console.log(e);
		if( e.code == "KeyS" && e.ctrlKey )
			this.save();
		else if( e.code == "KeyO" && e.ctrlKey )
			this.toggleConsole();
		else if( e.code == "KeyQ" && e.ctrlKey )
        {
            if( document.activeElement == this.console_input )
                this.current_file.editor.focus();
            else
                this.console_input.focus();
        }
		else if( (e.code == "KeyP" || e.code == "Enter") && e.ctrlKey )
			this.execute();
		else if( e.keyCode >= 49 && e.keyCode <= 58 && e.altKey && e.ctrlKey )
        {
            var file_info = this.files[e.keyCode - 49];
            if(file_info)
                this.open( file_info.name );
        }
		else
			return true; //release the event so monaco can process it

		e.preventDefault();
		e.stopPropagation();
		e.stopImmediatePropagation();
	},

    onEditorKey: function(e)
    {
        //console.log("ed key",e);
    },

	onFileAdded: function( filename, content )
	{
		var file_info = this.files_by_name[ filename ];
		if(!file_info)
		{
			file_info = { name: filename, content: null };
			this.files.push( file_info );
			this.files_by_name[ filename ] = file_info;
		}

		if( content != null )
			file_info.content = content;

		if(!file_info.file_element)
		{
            var container = document.querySelector("#open-files");
			var t = filename.split("/");
			var basename = t[ t.length - 1 ] || ".";
			var element = document.createElement("div");
			element.className = "filename";
			if( file_info.content === null )
				element.className += " notloaded";
			element.innerHTML = "<span class='number'></span>"+basename+"<span class='close'>&#10005;</span>";
			container.appendChild(element);
			element.dataset["filename"] = filename;
			element.querySelector(".close").addEventListener("click", function(e){
                var is_saved = file_info._exist !== false;
				if( is_saved || confirm("File not saved, are you sure you want to "+(e.shiftKey ? "delete" : "close")+" it?\nData will be lost." ) )
				{
					if(e.shiftKey)
						WIDE.delete( element.dataset["filename"] );
					else
						WIDE.close( element.dataset["filename"] );
				}
                e.stopImmediatePropagation();
                e.stopPropagation();
			});
			element.addEventListener("click", function(e){
				WIDE.open( this.dataset["filename"], true );
			});
			file_info.file_element = element;
            var entries = container.querySelectorAll(".filename .number");
            for(var i = 0; i < entries.length && i < 10; ++i)
                entries[i].innerHTML = i+1;
		}

        return file_info;
	},

	onFileLoaded: function( filename, content )
	{
		var file_info = this.files_by_name[ filename ];
		if(!file_info)
            return null; //a file arrives but is no longer in the editor

		if(!file_info.file_element)
			throw("file entry without element?");
		file_info.file_element.classList.remove("notloaded");
		file_info.content = content;
        	file_info._exist = true;
		if(this.current_file && this.current_file.name == filename)
			file_info.editor.setValue(content);
		return file_info;
	},

	onShowFolderFiles: function( folder, files, project, skip_log )
	{
		var container = document.querySelector("#folder-files");
		container.innerHTML = "<div class='project-title'>"+(project || "")+"<span class='buttons'><span class='trash' title='remove key'><svg class='icon'><use xlink:href='#si-bootstrap-trash'/></svg></span><span class='close' title='close list'>✕</span></span></div>";
		container.querySelector(".trash").addEventListener("click",function(e){	if(confirm("Are you sure you want to remove the key to this project?")) WIDE.setKey(""); WIDE.list(); });
		container.querySelector(".close").addEventListener("click",function(e){	WIDE.toggleFiles(); });
		container.classList.remove("loading");
		folder = this.cleanPath( folder );
		this.current_folder = folder;
		var tree = folder.split("/");
		var folders = [{ name: "root folder", is_dir: true, is_parent: true, fullpath: "." }];
		for( var i = 0; i < tree.length; ++i )
			folders.push({ name: tree[i], is_dir: true, is_parent: true, is_current: i == tree.length - 1, fullpath: tree.slice(0,i+1).join("/") });
		var files_and_folders = folders.concat( files );
		var folders = files_and_folders.filter(function(a){ return a.is_dir; });
		var files = files_and_folders.filter(function(a){ return !a.is_dir; });
		files_and_folders = folders.concat(files);

		for(var i = 0; i < files_and_folders.length; ++i)
		{
			var file = files_and_folders[i];
			if(!file.name)
				continue;
			if( !file.is_parent && !skip_log )
				this.toConsole( file.name + ( !file.is_dir ? " <span class='size'>" + file.size + "b</span>" : "/"), file.is_dir ? "folder" : "file" , true );
			var fullpath = this.cleanPath( file.fullpath || folder + "/" + file.name );
			var element = document.createElement("div");
			element.className = "filename";
			if( file.is_dir )
				element.classList.add("folder");
			if( file.is_parent )
				element.classList.add("parent-folder");
			if( file.is_current )
				element.classList.add("current-folder");
			var icon = "bootstrap-file";
			if( file.is_dir ) 
				icon = "bootstrap-folder-close";
			if( file.is_parent ) 
				icon = "bootstrap-play";
			element.innerHTML = '<svg class="icon"><use xlink:href="#si-'+icon+'" /></svg> ' + file.name;
			if( this.files_by_name[ "/" + fullpath ] )
				element.classList.add("open");
			container.appendChild(element);
			var depth = !fullpath ? 0 : fullpath.split("/").length;
			element.style.marginLeft = (depth * 5) + "px";
			element.dataset["is_dir"] = file.is_dir;
			element.dataset["filename"] = file.name;
			element.dataset["fullpath"] = fullpath || ".";
			var can_be_opened = true;
			if(file.mime_type)
			{
				element.dataset["mime_type"] = file.mime_type;
				var type = file.mime_type.split("/")[0].toLowerCase();
				can_be_opened = type != "image" && type != "audio" && type != "video";
				if(!can_be_opened)
					element.classList.add("blocked");
			}
			element.setAttribute("title",fullpath);
			if(can_be_opened)
				element.addEventListener("click", function(e){
					if( this.dataset["is_dir"] == "true" )
						WIDE.list( "/" + this.dataset["fullpath"] );
					else
					{
						if( WIDE.files_by_name[ "/" + this.dataset["fullpath"] ] )
						{
							if( !confirm("File already open, changes will be lost, are you sure?") )
								return;
						}
						WIDE.load( "/" + this.dataset["fullpath"], null, true );
					}
				});
		}

		//new file
		var new_file_text = '<svg class="icon"><use xlink:href="#si-elusive-file-new" /></svg> new file';
		var element = document.createElement("div");
		element.className = "filename new-file";
		element.innerHTML = new_file_text;
		element.addEventListener("click", function(e){
			element.innerHTML = "<input type='text' placeHolder='type filename'/>";
			var input = element.querySelector("input");
			input.addEventListener("keydown",function(e){
				if(e.keyCode != 13)
					return;
				e.preventDefault();
				var filename = this.value;
				if(filename)
				{
					WIDE.toggleFiles();
					WIDE.create( WIDE.cleanPath(folder + "/" + filename),"",true );
				}
				setTimeout(function(){ element.innerHTML = new_file_text; },1);
			});
			input.focus();
			input.addEventListener("blur", function(e){
				setTimeout(function(){ element.innerHTML = new_file_text; },1);
			});
		});
		container.appendChild( element );

		//new folder
		var new_folder_text = '<svg class="icon"><use xlink:href="#si-elusive-file-new" /></svg> new folder';
		var element = document.createElement("div");
		element.className = "filename new-file";
		element.innerHTML = new_folder_text;
		element.addEventListener("click", function(e){
			element.innerHTML = "<input type='text' placeHolder='type foldername'/>";
			var input = element.querySelector("input");
			input.addEventListener("keydown",function(e){
				if(e.keyCode != 13)
					return;
				e.preventDefault();
				var foldername = this.value;
				if(foldername)
				{
					//WIDE.toggleFiles();
					WIDE.createFolder( WIDE.cleanPath(folder + "/" + foldername));
				}
				setTimeout(function(){ element.innerHTML = new_folder_text; },1);
			});
			input.focus();
			input.addEventListener("blur", function(e){
				setTimeout(function(){ element.innerHTML = new_folder_text; },1);
			});
		});
		container.appendChild( element );		
	},

    onFileSaved: function( filename )
    {
		var file_info = this.files_by_name[ filename ];
		if(!file_info)
			throw("cannot call onFileSaved if there is no file entry");
        if(file_info.file_element)
            file_info.file_element.classList.remove("modified");
        file_info._exist = true;
    },

    onVisibleFileChange: function()
    {
		if(!this.current_file)
			return;
        if(this.current_file.onLeave)
            this.current_file.onLeave( this.current_file.editor ? this.current_file.editor.getValue() : null );
		this.current_file.file_element.classList.remove("selected");
    },

	onContentChange: function(file_info)
	{
		if(!file_info)
			return;
		file_info.file_element.classList.add("modifyed");
        this.editor_header.classList.add("modifyed");
        //change title too?
	},

	cleanPath: function(path)
	{
		var t = path.split("/");
		t = t.filter( function(v) { if (v == ".") return false; return !!v; } );
		//apply "../", sometimes this gives me problems
		var result = [];
		for(var i = 0; i < t.length; i++)
		{
			if(t[i] == "..")
				result.pop();
			else
				result.push( t[i] );
		}

		return result.join("/");
	},

	processFolder: function( folder, default_to_root )
	{
		if( !folder )
			return default_to_root ? "./" : this.current_folder;
		if( folder[0] == "/" )
			return folder;
		return this.cleanPath( this.current_folder + "/" + folder) || "./";
	},

	toggleFiles: function()
	{
        this.files_list_open = !this.files_list_open;

        if( this.files_list_open )
        {
            document.querySelector("#folder-files").style.display = "";
            document.querySelector("#open-files").style.display = "none";
            document.querySelector(".list-files-button").classList.add("selected");
            this.list();
        }
        else
        {
            document.querySelector("#folder-files").style.display = "none";
            document.querySelector("#open-files").style.display = "";
    		document.querySelector(".list-files-button").classList.remove("selected");
        }
	},

    toConsole: function( str, className, is_html )
    {
        var elem = document.createElement("div");
        if(is_html)
            elem.innerHTML = str;
        else
            elem.innerText = str;
        elem.className = "msg " + (className || "");
        this.console_element.appendChild(elem);
        if( this.console_element.childNodes.length > 1000)
            this.console_element.removeChild( this.console_element.childNodes[0] );
        this.console_element.scrollTop = 1000000;
    },

    toggleConsole: function()
    {
        this.console_open = !this.console_open;
		document.querySelector("#sidebar .header button.toggle-console").classList.toggle("selected");
        document.querySelector("#container").classList.toggle("show_console");
        this.onResize();
    },

    editSettings: function()
    {
        if( this.files_by_name[ "settings.json" ] )
        {
            this.open("settings.json",true);
            return;
        }
        var data = JSON.stringify( this.settings, null, " " );
        var file_info = this.onFileAdded("settings.json", data);
        file_info.internal = true;
        file_info.onLeave = inner;
        file_info.onExecute = inner;
        
        function inner(content){
            var new_settings = JSON.parse(content);
            WIDE.settings = new_settings;
            console.log("settings modifyed");
        }
        this.open("settings.json",true);
    }
};

// Bridge between client and server, you can create your own if you do not want to use server.php
// *************************************************************************************************
var WIDE_SERVER = {
	url: "server.php",
	key: "",

	load: function( filename, callback, callback_error )
	{
        if(!WIDE.key)
        {
            console.error("file cannot be loaded, no key set. Type 'key YOUKEY' in the command bar to have access to the server.");
            return false;
        }

		var form = new FormData();
		form.append( "action", "load" );
		form.append( "filename", filename );
        form.append( "key", WIDE.key );
		var headers = new Headers();
		headers.append('pragma', 'no-cache');
	 	headers.append('cache-control', 'no-cache');
		var info = { method: 'POST',  body: form, headers: headers };

		fetch( WIDE.settings.server_url, info ).then(function(resp){
			if(resp.status != 200)
				throw Error(resp.statusText);
			var headers = headersToObject( resp.headers );
			return resp.text();
		}).then( function(data){
			if(callback)
				callback( filename, data );
		}).catch(function(err){
			console.error("file not found: " + filename );
			if(callback_error)
				callback_error( filename )
		});

        return true;
	},

	save: function( filename, content, callback, callback_error )
	{
        if(!WIDE.key)
        {
            console.error("file cannot be saved, no key set");
            return;
        }            

		var form = new FormData();
		form.append("action","save");
		form.append("filename",filename);
		form.append("content",content);
        form.append("key", WIDE.key );
		var info = { method: 'POST', body: form };

		fetch( WIDE.settings.server_url, info ).then(function(resp){
			return resp.text(); 
		}).then( function(data){
			var r = JSON.parse(data);
			if(r.status == 1)
			{
				if(callback)
					callback(filename);
			}
			else
			{
				console.error( r.msg );
				if(callback_error)
					callback_error(filename, r.msg);
			}
		});
	},

	saveFolder: function(folder, foldername, callback, callback_error )
	{
        if(!WIDE.key)
        {
            console.error("folder cannot be saved, no key set");
            return;
        }            

		var form = new FormData();
		form.append("action","mkdir");
		form.append("folder",folder);
		form.append("filename",foldername);
        form.append("key", WIDE.key );
		var info = { method: 'POST', body: form };

		fetch( WIDE.settings.server_url, info ).then(function(resp){
			return resp.text(); 
		}).then( function(data){
			var r = JSON.parse(data);
			if(r.status == 1)
			{
				if(callback)
					callback(foldername);
			}
			else
			{
				console.error( r.msg );
				if(callback_error)
					callback_error(foldername, r.msg);
			}
		});
	},	

	"delete": function( filename, callback, callback_error )
	{
		if(!filename)
			return false;

        if(!WIDE.key)
        {
            console.error("file cannot be deleted, no key set");
            return false;
        }

		var form = new FormData();
		form.append("action","delete");
		form.append("filename",filename);
        form.append("key", WIDE.key );
		var info = { method: 'POST', body: form };

		fetch( WIDE.settings.server_url, info ).then(function(resp){
			return resp.text();
		}).then( function(data){
			var r = JSON.parse(data);
			if(r.status == 1)
			{
				console.log(r.msg);
				if(callback)
					callback(filename);
			}
			else
			{
				console.error( r.msg );
				if(callback_error)
					callback_error(filename);
			}
		});

		return true;
	},

	move: function( filename, new_filename, callback, callback_error )
	{
		if(!filename || !new_filename)
			return false;

        if(!WIDE.key)
        {
            console.error("file cannot be moved, no key set");
            return false;
        }              

		var form = new FormData();
		form.append("action","move");
		form.append("filename",filename);
		form.append("new_filename", new_filename);
        form.append("key", WIDE.key );
		var info = { method: 'POST', body: form };

		fetch( WIDE.settings.server_url, info ).then(function(resp){
			return resp.text();
		}).then( function(data){
			var r = JSON.parse(data);
			if(r.status == 1)
			{
                if(callback)
					callback( filename, new_filename );
			}
			else
			{
				console.error( r.msg );
                if(callback_error)
					callback_error( filename, new_filename );
			}
		});

		return true;
	},

	list: function( folder, skip_log, callback, callback_error )
	{
        if(!WIDE.key)
        {
            return false;
        }

		var form = new FormData();
		form.append("action","list");
		form.append("folder", folder );
        form.append("key", WIDE.key );
		var info = { method: 'POST', body: form };

		fetch( WIDE.settings.server_url, info ).then(function(resp){
			return resp.text();
		}).then( function(data){
			var r = JSON.parse(data);
			if(r.status == 1)
            {
				if(callback)
					callback( r.folder, r.files, r.project, skip_log );
            }
			else
			{
				console.error( r.msg );
				if(callback_error)
					callback_error(r.msg);
			}
		});
	},

    autocomplete: function( fullpath, callback, callback_error )
    {
        if(!WIDE.key)
            return false;

		var filename = fullpath.split("/").pop();

		var form = new FormData();
		form.append("action","autocomplete");
		form.append("filename", fullpath );
        form.append("key", WIDE.key );
		var info = { method: 'POST', body: form };

		fetch( WIDE.settings.server_url, info ).then(function(resp){
			return resp.text();
		}).then( function(data){
			var r = JSON.parse(data);
			if(r.status == 1)
			{
				var shared = "";
				if( r.data.length == 1 )
					shared = r.data[0];
				else if( r.data.length > 1 )
					shared = sharedStart( r.data );
				if(callback)
					callback( r.data, shared.substr( filename.length ) );
			}
			else
			{
				console.error( r.msg );
				if(callback_error)
					callback_error(r.msg);
			}
		});
		return true;
    }
}

//commands
WIDE.commands.open = WIDE.commands.load = function( cmd, t ) { WIDE.load( WIDE.current_folder + "/" + t[1], null, true ); } 
WIDE.commands.save = function( cmd, t ) { WIDE.save(); }
WIDE.commands.new = function( cmd, t ) { WIDE.create(t[1],"",true); }
WIDE.commands.rm = WIDE.commands.delete = function( cmd, t ) { WIDE.delete( WIDE.current_folder + "/" + t[1]); }
WIDE.commands.mv = WIDE.commands.move = function( cmd, t ) { WIDE.move( WIDE.current_folder + "/" + t[1], WIDE.current_folder + "/" + t[2] ); }
WIDE.commands.ls = WIDE.commands.list = function( cmd, t ) { WIDE.list( t[1] ); }
WIDE.commands.cd = function( cmd, t ) { WIDE.list( t[1], true, function(){ WIDE.toConsole( WIDE.current_folder,"filename folder"); }); }
WIDE.commands.close  = function( cmd, t ) { WIDE.close(t[1]); }
WIDE.commands.reset  = function( cmd, t ) { WIDE.reset(); }
WIDE.commands.execute = function( cmd, t ) { WIDE.execute(); }
WIDE.commands.reload = function( cmd, t ) { for(var i in WIDE.files) WIDE.load( WIDE.files[i].name ); }
WIDE.commands.key = function( cmd, t ) { WIDE.setKey(t.slice(1).join(" ")); }
WIDE.commands.tempkey = function( cmd, t ) { WIDE.setKey(t.slice(1).join(" "),false); }
WIDE.commands.console = function( cmd, t ) { WIDE.toggleConsole(); }
WIDE.commands.clear = function( cmd, t ) { WIDE.console_element.innerHTML = ""; }
WIDE.commands.pwd = function( cmd, t ) { WIDE.toConsole( WIDE.current_folder + "/", "filename folder" ); }
WIDE.commands.toggleFiles = function( cmd, t ) { WIDE.toggleFiles(); }
WIDE.commands.settings = function( cmd, t ) { WIDE.editSettings(); }

//buttons
WIDE.buttons.push({ name:"new", icon:"elusive-file-new", command: "new" });
WIDE.buttons.push({ name:"open file", className:'list-files-button', icon:"bootstrap-folder-open", command: "toggleFiles" });
WIDE.buttons.push({ name:"save current", icon:"bootstrap-import", command: "save" });
WIDE.buttons.push({ name:"show console", className:"toggle-console", icon:"icomoon-terminal", command: "console" });
WIDE.buttons.push({ name:"execute code", icon:"bootstrap-play", command: "execute" });
WIDE.buttons.push({ name:"settings", icon:"bootstrap-menu-hamburger", command: "settings" });

//helpers
function queryforEach( selector,callback ) { var list = document.querySelectorAll(selector); for(var i = 0;i < list.length; ++i) callback( list[i] ); }
function sharedStart(array){ var A= array.concat().sort(), a1= A[0], a2= A[A.length-1], L= a1.length, i= 0; while(i<L && a1.charAt(i)=== a2.charAt(i)) i++; return a1.substring(0, i); }
function headersToObject(headers) { var array = Array.from( headers.entries() ); var r = {}; for(var i = 0; i < array.length; ++i) r[ array[i][0] ] = array[i][1]; return r; }

//redirect console
console._log = console.log;
console._warn = console.warn;
console._error = console.error;
console._clear = console.clear;
console.log = function(){ console._log.apply(console,arguments); WIDE.toConsole( Array.prototype.slice.call(arguments).map(function(a){ return String(a); }).join(","),"log");  };
console.warn = function(){ console._warn.apply(console,arguments); WIDE.toConsole( Array.prototype.slice.call(arguments).map(function(a){ return String(a); }).join(","),"warn"); };
console.error = function(){ console._error.apply(console,arguments); WIDE.toConsole( Array.prototype.slice.call(arguments).map(function(a){ return String(a); }).join(","),"error"); };
console.clear = function(){ console._clear(); WIDE.commands.clear(); };

WIDE.init();
console.log("WIDE Editor Console");
console.log("************************************************");

