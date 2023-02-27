import requests
import time
import sys
from lxml import etree
from bs4 import BeautifulSoup

# Urls could be stored in a db or similar other source
URLs = ["https://www.gov.uk/search/policy-papers-and-consultations?content_store_document_type%5B%5D=policy_papers&order=updated-newest","https://www.gov.uk/search/policy-papers-and-consultations?content_store_document_type%5B%5D=policy_papers&order=updated-newest&page=2"
,"https://www.gov.uk/search/policy-papers-and-consultations?content_store_document_type%5B%5D=policy_papers&order=updated-newest&page=3"]
GovLinks = []

for URL in URLs:
    page = requests.get(URL)
    soup = BeautifulSoup(page.content, "html.parser")
    dom = etree.HTML(str(soup))

    # Loop through our etree dom and get all hrefs
    for link in dom.xpath("//div[contains(@class, 'finder-results')]//li[contains(@class, 'gem-c-document-list__item')]/a"):
        newUrl = link.attrib['href']
        if (newUrl.startswith('/')):
            newUrl = "https://www.gov.uk" + link.attrib['href']
        if newUrl not in GovLinks:
            GovLinks.append(newUrl)

    # We could add a time delay here to prevent visting the same website too often

# Alternate method for getting links using pure bs4
#    for link in soup.find_all('a'):
#        newUrl = link.get('href')
#        if (newUrl.startswith('/')):
#            newUrl = "https://www.gov.uk" + link.get('href')
#        if newUrl not in GovLinks:
#            GovLinks.append(newUrl)

# Part 2 
firstFifty = GovLinks[:50]

govData = []

for URL in firstFifty:
    page = requests.get(URL)
    soup = BeautifulSoup(page.content, "html.parser")

    dom = etree.HTML(str(soup))

    # Xpath data to loop through in each location
    dataDets = [
        {
            'name':'title',
            'xPath':"//meta[@property='og:title']",
            'type':"attrib['content']"
        },
        {
            'name':'authors',
            'xPath':"//div[contains(@class, 'gem-c-metadata')]//a[contains(@class, 'govuk-link')]",
            'type':'text'
        },
        {
            'name':'type',
            'xPath':"//meta[@property='og:type']",
            'type':"attrib['content']"
        }
    ]

    # Loop through our data/xpath details and pull out the relevant types dynamically
    currData = {}
    for dataDet in dataDets:
        currData[dataDet['name']] = []
        for currXpath in  dom.xpath(dataDet['xPath']):
            varname = 'currXpath.' + dataDet['type']
            currData[dataDet['name']].append(eval(varname))

    # 1) Get Title
    #for title in dom.xpath("//meta[@property='og:title']"):
    #    currTitle = title.attrib['content']

    # 2) Get authors
    #authors = []
    #for author in dom.xpath("//div[contains(@class, 'gem-c-metadata')]//a[contains(@class, 'govuk-link')]"):
    #    authors.append(author.text)

    #currData = {
    #    'title': currTitle,
    #    'authors': authors
    #}
    govData.append(currData)

print(govData)
