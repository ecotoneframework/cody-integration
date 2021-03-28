<?php
declare(strict_types=1);

namespace Ecotone\InspectioCody;

use Ecotone\InspectioCody\Code\Command\CommandClassGenerator;
use Ecotone\InspectioCody\Code\LocalSystemFileLoader;
use Ecotone\InspectioCody\Config\Command;
use Ecotone\InspectioCody\Metadata\CommandMetadata;
use Ecotone\InspectioCody\Metadata\MetadataFactory;
use EventEngine\InspectioCody\Board\BaseHook;
use EventEngine\InspectioCody\Http\Message\Response;
use Psr\Http\Message\ResponseInterface;
use EventEngine\InspectioGraphCody as Cody;

final class CommandHook extends BaseHook
{
    private string $successDetails;

    private MetadataFactory $metadataFactory;
    private Command $config;

    public function __construct(Command $config)
    {
        parent::__construct();
        $this->config = $config;
        $this->metadataFactory = new MetadataFactory();
    }

    public function __invoke(Cody\Node $command, Context $ctx): ResponseInterface
    {
        $this->successDetails = "Checklist\n\n";

        /** @var CommandMetadata $metadata */
        $metadata = ($this->metadataFactory)($command);

        $analyzer = new Cody\EventSourcingAnalyzer($command, $this->config->getFilterClassName(), $this->metadataFactory);
        $fileLoader = new LocalSystemFileLoader($ctx->parser);

        $valueObjectGenerator = $this->config->getValueObjectGenerator($ctx, $fileLoader);

        $commandClassGenerator = new CommandClassGenerator(
            $this->config,
            $ctx->parser,
            $ctx->printer,
            $analyzer,
            $fileLoader,
            $valueObjectGenerator,
            $ctx->filesystem
        );

        $files = $commandClassGenerator->generate(
            Cody\Vertex::fromCodyNode($command, $this->config->getFilterClassName(), $this->metadataFactory),
            $metadata,
            $ctx
        );

        foreach ($files as $file) {
            $this->successDetails .= "✔️ File {$file['filename']} updated\n";
            $this->writeFile($file['code'], $file['filename']);
        }

        return Response::fromCody(
            "Wasn't easy, but command {$command->name()} should work now!",
            ['%c' . $this->successDetails, 'color: #73dd8e;font-weight: bold']
        );
    }
}
