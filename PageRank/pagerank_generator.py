import networkx as nx

PATH = "/solr-7.7.0/../NYTIMES/nytimes/"

g = nx.read_edgelist("edges_list.txt", create_using=nx.DiGraph())
pagerank = nx.pagerank(g, alpha=0.85, personalization=None, max_iter=30, tol=1e-06, nstart=None, weight='weight',
                       dangling=None)

with open("networkx_PageRankScores.txt", "w") as file_writer:
    for file, pr in pagerank.items():
        file_writer.write(PATH+file + "=" + str(pr)+"\n")

    file_writer.close()