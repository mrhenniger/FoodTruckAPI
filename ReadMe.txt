Hello!

My name is Mike Henniger, and this is my Food Truck API project.

As I am only supposed to be spend two or three hours on this project it will be an "abbreviated" project and I will use this document to describe what I have done and what I would have done differently if I were to take more time.

I am thinking of an application the user runs on their smartphone to find the food trucks closest to their current physical location.  What I have done here is develop the API for that app that accepts a key word like "taco" as the "want" parameter, and "lat" and "lon" coordinates which represent the user's current position (it is assumed the UI would be able to get the lat and lon from the OS).  The API then returns the information the user will need to find the closest food sources providing their wanted item in a json structure.  Since the user may not provide a want item string the API can find in the "FoodItems" column of the data set, the api also provides the next three closest food providers in the data set, this way the UI will always have something to display to the user.  It is also worth noting the number of matches to the want parameter and the alternates are kept to a maximum of three in each set.  This could easily be adjusted and perhaps could be set by the UI via another API or adding support for another parameter.

If this was a proper project, I would implement a MySQL table and import a select number subset of columns from the data file into the table.  In the interest of keeping this project to a reasonable length of time, I will just have the API parse the file each time (yes I know that is horrible, I am not proud of that).

Also, I am not getting into providing authentication (too much scope for this project in the short time frame).

The API parses the parameters "want", "lat" and "lon" from the URL and does some parameter checks.  Depending on what it finds it may return a 400 with an appropriate error message in the JSON.

The flat file from the City of San Francisco (converted to tab delimited text as I like the simplicity of tab delimited) is then opened (using the standard PHP fopen), and parsed line-by-line to find the closest matches and alternates.  These are then converted into JSON which has the categories 'matches' and 'others' each containing an array with up to three items.

It is also worth noting a 'status' field is provided in each returned JSON which will be either "success" or "failure".  Only the 'status' field with a values of "success" will have the sibling fields 'matches' and 'others'.

In addition, a file "utilities.php" is included in the project to help keep the code in the api itself short and clean.  I could have taken it further and written more elaborate utilities, but again there is the limitation of time.  I also included a css file I grabbed from another project.

I have deployed the project to an AWS instance (one that I use as a sandbox).  You can hit the API with this example URL...
http://54.215.136.169/apiFoodTruck.php?want=taco&lat=37.789302515723385&lon=-122.40120079571057
...which is looking for tacos near the intersection of Market and 2nd in down town SF.

This project does not include a proper UI.  Again, time.  I did however write a very basic UI which I used for testing, and you can find it here...
http://54.215.136.169/Samples.html
How did I use this basic UI for testing?  For example, you could go to the top of the flat file and get the lat (37.76537066931712), lon (-122.403907848212) and plug those into the form in the test UI.  Then put "onion rings" in the want field and search.  When you  do that "Natan's Catering" is presented at the top of the list.

If you wish you can deploy the project to your own server, just make sure the files are all in the same directory.

That is it.  I look forward to answering any questions you may have.

Regards,
Mike Henniger
