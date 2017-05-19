import pywikibot
import json
import urllib2
import pprint
import locale
from pywikibot import pagegenerators

# Set Locale
locale.setlocale(locale.LC_ALL, 'en_US.UTF-8')

# Get English Wikipedia
site = pywikibot.Site()
page = pywikibot.Page(site, u"User:TParis/Featured_Article_By_Length")

if pywikibot.Page(site, u"User:TPBot/Featured_Articles_By_Length").text != u"yes":
    print("Script not enabled")
    exit()

if page:
    url = "https://petscan.wmflabs.org/?psid=913449&format=json"
    data = json.load(urllib2.urlopen(url))
    #pprint.pprint(data)

    #Page Header
    text = "Articles in [[:Category:Featured articles]] sorted by page length (in bytes); data as of <onlyinclude>~~~~~</onlyinclude>.\n"
    text += "{| class=\"wikitable sortable plainlinks\" style=\"width:100%; margin:auto;\"\n"
    text += "|- style=\"white-space:nowrap;\"\n"
    text += "! No.\n"
    text += "! Article\n"
    text += "! Length\n"

    counter = 0

    for article in data[u'*'][0][u'a'][u'*']:
        #pprint.pprint(article)
        if article[u'namespace'] == 0:
            print(article[u'title'] + "\n")
            counter += 1
            text += "|-\n"
            text += "| " + str(counter) + "\n"
            text += "| [[" + article[u'title'].replace('_', ' ') + "]]\n"
            text += "| " + locale.format("%d", article['len'], grouping=True) + "\n"

    text += "|-\n"
    text += "|}"
    
    page.text = unicode(text)
    page.save(u"Updating featured articles by length")
else:
    print("Cannot open page for editing")


